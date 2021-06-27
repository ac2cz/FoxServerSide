DROP PROCEDURE IF EXISTS MoveHs1Payloads;

DELIMITER //
CREATE PROCEDURE MoveHs1Payloads(IN badReset int(11), IN newReset int(11), IN badUptime bigint(20))
BEGIN

/* Clean the data from all the tables */
update Fox6RTTELEMETRY set resets = newReset where resets = badReset and uptime = badUptime;
update Fox6MAXTELEMETRY set resets = newReset where resets = badReset and uptime = badUptime;
update Fox6MINTELEMETRY set resets = newReset where resets = badReset and uptime = badUptime;
update Fox6CANTELEMETRY set resets = newReset where resets = badReset and uptime = badUptime;
update Fox6UW_CAN_PACKET set resets = newReset where resets = badReset and uptime = badUptime;

END //
DELIMITER ;
