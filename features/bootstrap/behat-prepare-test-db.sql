-- This file prepares the test database for Behat scenarios. It is run before each one

-- We start out by truncating some tables:
TRUNCATE user;

-- Now add some test data
INSERT INTO user (email, display_name,password) VALUES ('dfergu15@jccc.edu', 'Dan McTest', 'baAy105yI1l35FcS6WfC3uqQH4XorZf2Cl8KKMqzrQ9X3sGZB1U4G');