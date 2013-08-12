-- This file prepares the test database for Behat scenarios. It is run before each one

-- We start out by truncating some tables:
-- TRUNCATE users;


-- Now add some test data
-- INSERT INTO users (email, prefix, firstName, lastName, password, role, college_id)
-- SELECT 'dfergu15@jccc.edu', 'Mr.', 'Dan', 'McTest',
-- '$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC',
-- 'admin', (SELECT id FROM colleges WHERE ipeds = '155210' LIMIT 1);

UPDATE users
set role = 'admin',
password = '$2y$10$abzhQMM078raedPPvvLYvO3vnbTwOhgRnbG4jBTDq8wfIxeXd93rO'
WHERE email = 'dfergu15@jccc.edu';


-- Open up enrollment
UPDATE studies SET enrollmentOpen = true WHERE id = 2;
UPDATE studies SET enrollmentOpen = true WHERE id = 3;

-- Add the jccc/mrss observation and subscription
INSERT INTO `observations` (college_id, year) VALUES (1,2013);
INSERT INTO `subscriptions` VALUES (1,1,1,2013,'pending','invoice',1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'John Doe','Chief Tester','2013-08-12 08:45:30');
