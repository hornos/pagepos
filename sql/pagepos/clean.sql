-- drops

-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION cobra_seconds( varchar ) FROM pagepos_admin;
REVOKE ALL PRIVILEGES ON FUNCTION cobra_time() FROM GROUP pagepos;
REVOKE ALL PRIVILEGES ON FUNCTION cobra_microtime() FROM GROUP pagepos;
REVOKE ALL PRIVILEGES ON FUNCTION cobra_timestamp() FROM GROUP pagepos;
