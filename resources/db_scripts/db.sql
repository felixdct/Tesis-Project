drop database if exists authenticatedb;
create database authenticatedb;
use authenticatedb;

create table users(users_userId int(10) unsigned NOT NULL AUTO_INCREMENT,
		  users_nickName varchar(40) NOT NULL,
                  users_userName varchar(40) NOT NULL,
	          users_lastName varchar(40) NOT NULL,
	          users_email    varchar(60) NOT NULL,
		  users_active   tinyint(1)  NOT NULL,
	          PRIMARY KEY (users_userId) using btree,
		  UNIQUE KEY  (users_nickName),
	          UNIQUE KEY  (users_email)	
	          );

create table credentials(credentials_userId int(10) unsigned NOT NULL, 
                         credentials_passwd varchar(100) NOT NULL,
			 credentials_token varchar(40) NOT NULL,
			 credentials_fingerprint blob,
			 credentials_qrhash text,
			 PRIMARY KEY (credentials_userId) using btree,
			 CONSTRAINT `FK_CREDENTIALS_USERID` FOREIGN KEY (`credentials_userId`) 
		              REFERENCES `users` (`users_userId`)
			      ON UPDATE CASCADE
			      ON DELETE CASCADE
		         );

create table systemTrack(system_track_userId int(10) unsigned NOT NULL,
	                  system_track_op int(1) unsigned NOT NULL, 
			  system_track_op_state  tinyint(1) NOT NULL, 
                          system_track_errCode int(4) unsigned NOT NULL,
			  system_track_last_updated DATETIME NOT NULL,
		          PRIMARY KEY (system_track_userId),
			  CONSTRAINT `FK_SYSTEMTRACK_USERID` FOREIGN KEY (`system_track_userId`) 
		          	    REFERENCES `users` (`users_userId`)
				    ON UPDATE CASCADE
				    ON DELETE CASCADE
		         );


#remove inactive user every hour
create event e_hourly
ON SCHEDULE
EVERY 1 DAY
COMMENT 'Clear users that has not been activated.'
DO
	delete from users where users_active = 0;

#update rows in systemTrack table which last time updated is more than 1 day.
#update as system_track_op_state = 3 (FAIL_STATE_OPERATION)
#update as system_track_errCode = 4 (CANCEL_ERROR_TIMEOUT)
#Then, it means that this event will remove all the operation in the system that takes
#more than 1 day to be completed.
create event e_day
ON SCHEDULE
EVERY 12 HOUR
COMMENT 'Cleaning operation in progress'
DO
	update systemTrack set system_track_op_state = 3, system_track_errCode = 4
	where system_track_last_updated < DATE_SUB(NOW(), INTERVAL 1 DAY);

SET GLOBAL event_scheduler = ON;
SET @@global.event_scheduler = ON;
SET GLOBAL event_scheduler = 1;
SET @@global.event_scheduler = 1;
