-- Create database 
CREATE DATABASE phpproject CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Create tables
USE phpproject;

CREATE OR REPLACE TABLE event_type (
    event_type_id INT(2) UNSIGNED PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL
);


-- Role
CREATE OR REPLACE TABLE role (
    role_id INT(1) UNSIGNED PRIMARY KEY,
    role VARCHAR(50) NOT NULL
);

-- ALTER TABLE role ADD CONSTRAINT check_role_id CHECK(role_id<=4);

# Privilege
CREATE OR REPLACE TABLE privilege (
    privilege_id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    privilege VARCHAR(50) NOT NULL
);
# m-n relationship table between Role and Privilege
CREATE OR REPLACE TABLE role_privilege (
    role_id INT(1) UNSIGNED,
    privilege_id INT(2) UNSIGNED,
    FOREIGN KEY (role_id) REFERENCES role(role_id),
    FOREIGN KEY (privilege_id) REFERENCES privilege(privilege_id),
    CONSTRAINT pk_role_privilege PRIMARY KEY (role_id, privilege_id)
);

# User status; Active 1, Disactive 2, Locked 0
CREATE OR REPLACE TABLE status (
    status_id INT(1) UNSIGNED PRIMARY KEY,
    status VARCHAR(20) NOT NULL
    -- CHECK (status_id <= 2)
);
-- ALTER TABLE status ADD CONSTRAINT check_status_id CHECK(role_id<=2);

-- User
CREATE OR REPLACE TABLE `user` (
user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
login_name VARCHAR(50) NOT NULL, 
firstname VARCHAR(50) NOT NULL,
lastname VARCHAR(50) NOT NULL,
email VARCHAR(100)  NOT NULL,
telephone CHAR(12),                             # Format of NNN-NNN-NNNN
cellphone CHAR(12),                             # Format of NNN-NNN-NNNN
status_id INT(1) UNSIGNED DEFAULT 1 NOT NULL,
created_at DATETIME NOT NULL,                   # user created datetime
modified_at DATETIME,                           # user modified datetime
lastlogin_at DATETIME,                          # last login datetime
locked_at DATETIME,                             # user locked datetime
FOREIGN KEY (status_id) REFERENCES status(status_id)
);
ALTER TABLE `user`
ADD UNIQUE (login_name),
ADD UNIQUE (email),
ADD UNIQUE (telephone),
ADD UNIQUE (cellphone);

-- Event table
CREATE OR REPLACE TABLE `event` (
    event_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type_id INT(4) UNSIGNED, 
    date_created DATETIME, 
    user_id INT(6) UNSIGNED,
    -- flag BOOLEAN,
    FOREIGN KEY (event_type_id) REFERENCES event_type(event_type_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);
-- Indicate whether the failing login event is within the timespan of locking an account
ALTER TABLE `event` ADD truncate BOOLEAN DEFAULT 0;
-- Authentication
CREATE OR REPLACE TABLE authentication (
    auth_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    password VARCHAR(50) NOT NULL,  
    date_start DATETIME NOT NULL,
    date_end DATETIME NOT NULL,
    salt VARCHAR(13) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id) 
);

-- ALTER TABLE user ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
# m-n relationship table between User and Role
CREATE OR REPLACE TABLE user_role (
    user_id INT(6) UNSIGNED,    
    role_id INT(1) UNSIGNED,
    FOREIGN KEY (user_id) REFERENCES user(user_id),    
    FOREIGN KEY (role_id) REFERENCES role(role_id),
    CONSTRAINT pk_user_role PRIMARY KEY (user_id, role_id)    
);

-- Create User who only has limited DML access right to the database myproject
CREATE USER 'admin'@'localhost' IDENTIFIED BY '123456';
GRANT ALL PRIVILEGES ON phpproject.* TO 'admin'@'localhost';
CREATE USER 'leijinmin'@'localhost' IDENTIFIED BY '123456';
GRANT SELECT,DELETE,INSERT,UPDATE ON phpproject.* TO 'leijinmin'@'localhost';
GRANT EXECUTE ON phpproject.* TO 'leijinmin'@'localhost';
FLUSH PRIVILEGES;

-- Insert to status table
INSERT INTO status values (1,'Actif');
INSERT INTO status values (2,'Désactivé');
INSERT INTO status values (0,'Verrouillé');

-- Insert to role table
INSERT INTO role values (0,'Administrateur');
INSERT INTO role values (1,'Régulier');
INSERT INTO role values (2,'Modérateur');
INSERT INTO role values (3,'Invité');
INSERT INTO role values (4,'Auditeur');

-- Insert to event_type table
INSERT INTO event_type values (0,"Sign in");
INSERT INTO event_type values (1,"Successful login");
INSERT INTO event_type values (2,"Failed login");


-- Create the view for retrieving the user info, password, role, the times of unsuccessful logins since last successful login

-- Mysql cannot create the view with subqueries, a workaround for this is to create temporary views
-- https://blog.gruffdavies.com/2015/01/25/a-neat-mysql-hack-to-create-a-view-with-subquery-in-the-from-clause/
CREATE OR REPLACE VIEW view_user_role AS
SELECT ur.user_id, GROUP_CONCAT(r.role_id SEPARATOR ', ') as role_id, GROUP_CONCAT(r.role SEPARATOR ', ') as role 
  FROM user_role ur INNER JOIN role r 
    ON ur.role_id = r.role_id GROUP by ur.user_id

CREATE VIEW view_user_passwordstartdate AS
SELECT user_id,max(date_start) as date_start FROM authentication GROUP BY user_id;

CREATE VIEW  view_user_latestpassword AS 
SELECT a1.user_id, a1.password, salt, a1.date_start, a1.date_end 
  FROM authentication a1 INNER JOIN view_user_passwordstartdate a2 
 ON a1.date_start=a2.date_start

CREATE OR REPLACE VIEW view_failing_login AS
SELECT user_id,COUNT(event_id) AS failing_login_count 
  FROM `event` 
  WHERE event_type_id=2     -- the unsuccessful login
    -- AND TIMEDIFF(NOW(), date_created) < '00:15:00'
    AND truncate = 1
GROUP BY user_id

-- Create view view_user
CREATE OR REPLACE VIEW view_user AS
SELECT u.user_id, u.login_name, u.firstname, u.lastname
     , u.email,u.telephone, u.cellphone,s.status_id,s.status
     , vr.role_id,vr.role,p.password,p.salt, p.date_start,p.date_end, l.failing_login_count
  FROM `user` u INNER JOIN view_user_role vr
    ON u.user_id = vr.user_id inner join status s 
    ON u.status_id = s.status_id inner join view_user_latestpassword p 
    ON u.user_id = p.user_id LEFT JOIN view_failing_login l
    ON u.user_id = l.user_id
 
       
-- Test view_user
SELECT * from view_user

-- Create the function to get MD5 coded salted-password
DELIMITER $$
CREATE FUNCTION func_salted_password(
      password VARCHAR(50)
    , salt VARCHAR(13))
RETURNS VARCHAR(128) 
DETERMINISTIC
BEGIN
    RETURN  MD5(CONCAT(password, salt));      -- md5 string of salted password
END $$
DELIMITER ;

-- Create the procedure for adding a new user
DELIMITER $$
CREATE PROCEDURE proc_create_user (
      login_name VARCHAR(50)
    , firstname VARCHAR(50)
    , lastname VARCHAR(50)
    , email VARCHAR(100)
    , telephone CHAR(12)
    , cellphone CHAR(12)
    , status_id INT(1)
    , role_id VARCHAR(100)
    , password VARCHAR(50))
BEGIN
   DECLARE counter INT DEFAULT 1;
   DECLARE count_separater INT DEFAULT 0;
   DECLARE last_user_id INT;
   DECLARE maxInt INT UNSIGNED;
   

    -- Insert into user table
   INSERT INTO `user`(`login_name`
   , `firstname`
   , `lastname`
   , `email`
   , `telephone`
   , `cellphone`
   , `status_id`
   , `created_at`, `modified_at`, `lastlogin_at`, `locked_at`) 
   values (login_name
   , firstname
   , lastname
   , email
   , telephone
   , cellphone
   , status_id
   , NOW(), NULL, NULL, NULL);

   SET last_user_id = LAST_INSERT_ID();
   SET count_separater = (SELECT length(role_id) - length(REPLACE(role_id,',','')));

    --  Insert into user_role table
   WHILE counter <= count_separater + 1 DO
        INSERT INTO `user_role`(`user_id`, `role_id`) values (last_user_id, SUBSTRING_INDEX(SUBSTRING_INDEX(role_id,',',counter),',',-1));
        SET counter = counter + 1;
   END WHILE;
    -- Generate md5 string of salted password
    SET maxInt = ~0;       -- Max int
    SET @salt = CONV(FLOOR(RAND() * maxInt), 10, 36);  -- salt string
    SET @password = func_salted_password(password,@salt);

    -- Insert into authentication table
    INSERT INTO authentication(`user_id`,`password`,`date_start`,`date_end`,`salt`) values (last_user_id, @password, NOW(), NOW() + INTERVAL 3 MONTH, @salt);

    -- Insert into event table
    INSERT INTO event(`event_type_id`,`date_created`, `user_id`) values (0, NOW(), last_user_id);

END $$
DELIMITER ;
-- Test procedure proc_create_user
call proc_create_user(
 'leij4' 
,'Jinmin'
,'Lei' 
,'leijinmin@gmail.com' 
,'111-222-3333' 
,'111-222-3333' 
,1 
,'1,2,4' 
,'111111');

-- Create procedure to decide whether locking the user account
DELIMITER $$
CREATE PROCEDURE proc_lock_user(p_user_id INT)
BEGIN
    SET @failedTimes = (SELECT  count(event_id) 
                        FROM    event 
                        WHERE   user_id=p_user_id 
                        AND     event_type_id=2     -- the unsuccessful login
                        AND     truncate = 1        -- the unsuccessful login should be counted to decide whether lock an account 
                        );
                        
                        -- AND     TIMEDIFF(NOW(), date_created) < '00:15:00' )

    IF @failedTimes >= 2 THEN
    -- Lock in the 3rd fail; 
    -- if keep trying logging in after >=3 fails, keep updating the locked_at time to the latest
    -- in case of no attempt of login within 15 minutes, status can be reset to 1 (unlock) 
        UPDATE `user` SET status_id=0, locked_at=NOW() WHERE user_id=p_user_id;
    END IF; 

    INSERT INTO `event`(`event_type_id`,`date_created`,`user_id`, `truncate`) values(2, NOW(), p_user_id, 1); 
END $$
DELIMITER ;

-- Decide whether unlock the user account
DELIMITER $$
CREATE PROCEDURE proc_unlock_user(
      IN p_user_id INT
    , IN p_locked_at DATETIME
    , OUT user_id INT)
BEGIN
        SET @lock_time_expired = TIMEDIFF(NOW(), p_locked_at) >= '00:15:00';
        IF @lock_time_expired THEN
            UPDATE `user` SET status_id=1, locked_at=NULL WHERE user_id=p_user_id;
            -- Reset the previous truncate to the default 0 
            UPDATE `event` SET `truncate`=0 WHERE truncate=1 AND user_id=p_user_id;
            INSERT INTO `event`(`event_type_id`,`date_created`,`user_id`) values(1, NOW(), p_user_id);
            SET user_id=p_user_id;
        ELSE
            call proc_lock_user(p_user_id);
            SET user_id=0;
        END IF;
END $$
DELIMITER ;

-- Create the function for verifying the success of login
DELIMITER $$
CREATE FUNCTION func_verify_login(
      p_login_name VARCHAR(50)
    , p_password VARCHAR(50))
RETURNS INT  
DETERMINISTIC
BEGIN

    SELECT user_id, salt, password 
      INTO @user_id, @salt, @password 
      FROM `view_user`
     WHERE `login_name`=p_login_name;

     IF @user_id IS NULL THEN
        RETURN -3;   -- The login name doesn't exist
     END IF;

    
    IF func_salted_password(p_password, @salt) != @password THEN
    -- Lock user according to the unsuccessful login history
        call proc_lock_user(@user_id);
        RETURN -4;   -- The passowrd doesn't match 
    END IF;


    SELECT status_id, locked_at 
      INTO @status_id, @locked_at
      FROM `user` 
     WHERE `login_name`=p_login_name;

    IF @status_id = 1 THEN
    -- successful login
        INSERT INTO `event`(`event_type_id`,`date_created`,`user_id`) values(1, NOW(), @user_id);
        RETURN @user_id;
    END IF;

    IF @status_id = 0 THEN    -- If the account is locked(0), verify if 15 minutes have elapsed.
        call proc_unlock_user(@user_id, @locked_at, @result);
        return @result;
    END IF;

    RETURN -@status_id;       -- The account is deactivated(-2)
 
END $$
DELIMITER ;




