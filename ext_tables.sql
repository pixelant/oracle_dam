#
# Extend table 'sys_file'
#
CREATE TABLE sys_file (
	tx_oracledam_id int(11) unsigned DEFAULT 0 NOT NULL,
	tx_oracledam_file_timestamp int(11) unsigned DEFAULT 0 NOT NULL,
	tx_oracledam_metadata_timestamp int(11) unsigned DEFAULT 0 NOT NULL,

	KEY oracledam (tx_oracledam_id)
);
