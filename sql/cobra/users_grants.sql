--
-- tables
--
GRANT SELECT, INSERT, UPDATE, DELETE ON users TO cobra_admin;
--
-- functions
--
GRANT EXECUTE ON FUNCTION users_read( varchar )                    TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_valid( varchar )                   TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_group_id( varchar )                TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_force_read( varchar )              TO cobra_admin;
GRANT EXECUTE ON FUNCTION users_increment_login_tries( varchar )   TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_reset_login_tries( varchar )       TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_login( varchar, varchar )          TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_logout( varchar )                  TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_gc()                               TO       cobra_admin;
GRANT EXECUTE ON FUNCTION users_gc()                               TO       cobra_gc;
GRANT EXECUTE ON FUNCTION users_check_online( varchar )            TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_check_passcode( varchar, varchar ) TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION users_set_last_action_time( varchar )    TO GROUP cobra_system;
