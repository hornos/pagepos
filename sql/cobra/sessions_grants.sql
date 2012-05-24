--
-- tables
--
GRANT SELECT, INSERT, UPDATE, DELETE ON sessions TO cobra_admin;
--
-- functions
--
GRANT EXECUTE ON FUNCTION sessions_read( varchar )               TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION sessions_force_read( varchar )         TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION sessions_expired( varchar )            TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION sessions_write( varchar, int, text )   TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION sessions_change_id( varchar, varchar ) TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION sessions_destroy( varchar )            TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION sessions_gc()                          TO cobra_admin;
GRANT EXECUTE ON FUNCTION sessions_gc()                          TO cobra_gc;
GRANT EXECUTE ON FUNCTION sessions_clean()                       TO cobra_admin;
