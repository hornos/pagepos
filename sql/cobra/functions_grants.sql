-- function grants
GRANT EXECUTE ON FUNCTION cobra_seconds( varchar ) TO       cobra_admin;
GRANT EXECUTE ON FUNCTION cobra_time()             TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION cobra_time()             TO GROUP cobra_application;
GRANT EXECUTE ON FUNCTION cobra_time()             TO       cobra_gc;
GRANT EXECUTE ON FUNCTION cobra_microtime()        TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION cobra_microtime()        TO GROUP cobra_application;
GRANT EXECUTE ON FUNCTION cobra_microtime()        TO       cobra_gc;
GRANT EXECUTE ON FUNCTION cobra_timestamp()        TO GROUP cobra_system;
GRANT EXECUTE ON FUNCTION cobra_timestamp()        TO GROUP cobra_application;
GRANT EXECUTE ON FUNCTION cobra_timestamp()        TO       cobra_gc;
