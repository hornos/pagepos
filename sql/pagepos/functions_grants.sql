-- access grants
GRANT EXECUTE ON FUNCTION cobra_seconds( varchar ) TO pagepos_admin;
GRANT EXECUTE ON FUNCTION cobra_time() TO GROUP pagepos;
GRANT EXECUTE ON FUNCTION cobra_microtime() TO GROUP pagepos;
GRANT EXECUTE ON FUNCTION cobra_timestamp() TO GROUP pagepos;
