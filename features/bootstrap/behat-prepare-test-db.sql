-- This file prepares the test database for Behat scenarios. It is run before each one

-- We start out by truncating some tables:
TRUNCATE users;


-- Now add some test data
INSERT INTO users (email, displayName,password) VALUES ('dfergu15@jccc.edu', 'Dan McTest', '$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC');
