DROP PROCEDURE IF EXISTS DeleteHs1Payloads;

DELIMITER //
CREATE PROCEDURE DeleteHs1Payloads(IN badReset int(11), IN badUptime bigint(20))
BEGIN

/* Clean the data from all the tables */
delete from Fox6RTTELEMETRY where resets = badReset and uptime = badUptime;
delete from Fox6MAXTELEMETRY where resets = badReset and uptime = badUptime;
delete from Fox6MINTELEMETRY where resets = badReset and uptime = badUptime;
delete from Fox6CANTELEMETRY where resets = badReset and uptime = badUptime;
delete from Fox6UW_CAN_PACKET where resets = badReset and uptime = badUptime;

END //
DELIMITER ;
