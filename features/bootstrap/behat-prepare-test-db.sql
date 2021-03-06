-- This file prepares the test database for Behat scenarios. It is run before each one

-- We start out by truncating some tables:
-- TRUNCATE users;


-- Now add some test data
-- INSERT INTO users (email, prefix, firstName, lastName, password, role, college_id)
-- SELECT 'dfergu15@jccc.edu', 'Mr.', 'Dan', 'McTest',
-- '$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC',
-- 'admin', (SELECT id FROM colleges WHERE ipeds = '155210' LIMIT 1);

-- Open up enrollment
UPDATE studies SET enrollmentOpen = true WHERE id = 2;
UPDATE studies SET enrollmentOpen = true WHERE id = 3;

-- Open up reports
UPDATE studies SET reportsOpen = true WHERE id = 2;

-- Add the jccc college
INSERT INTO `colleges` VALUES (1,'Johnson County Community College','155210','Overland P',NULL,NULL,'12345 College Blvd','KS','12345','OCB 204B',NULL) ON DUPLICATE KEY UPDATE id=id;

-- Add the jccc/mrss observation and subscription
INSERT INTO `observations` (college_id, year) VALUES (1,2013) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `subscriptions` VALUES (1,1,1,2013,'pending','invoice',1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'John Doe','Chief Tester','2013-08-12 08:45:30') ON DUPLICATE KEY UPDATE id=id;

UPDATE users
set role = 'admin',
  password = '$2y$10$abzhQMM078raedPPvvLYvO3vnbTwOhgRnbG4jBTDq8wfIxeXd93rO',
  college_id = 101
WHERE email = 'dfergu15@jccc.edu';
