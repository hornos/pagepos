--
-- tables
--
GRANT SELECT, INSERT, UPDATE, DELETE ON applications TO cobra_admin;
--
-- functions
--
GRANT EXECUTE ON FUNCTION applications_read( varchar )        TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION applications_valid( varchar )       TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION applications_force_read( varchar )  TO       cobra_admin;
