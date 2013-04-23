-- This file prepares the test database for Behat scenarios. It is run before each one

-- We start out by truncating some tables:
ALTER TABLE pages DROP FOREIGN KEY FK_2074E575E37ECFB0;
TRUNCATE users;
ALTER TABLE pages ADD CONSTRAINT FK_2074E575E37ECFB0 FOREIGN KEY (updater_id)
REFERENCES users (id);


-- Now add some test data
INSERT INTO users (email, displayName,password) VALUES ('dfergu15@jccc.edu', 'Dan McTest', '$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC');
