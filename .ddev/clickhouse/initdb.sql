-- Runs once on first init of the clickhouse data volume.
-- "db" mirrors the ddev MySQL database name: the sink connector maps the source
-- database name 1:1 onto ClickHouse. "altinity_sink_connector" holds the
-- connector's own offset + schema-history state tables.
CREATE DATABASE IF NOT EXISTS db;
CREATE DATABASE IF NOT EXISTS altinity_sink_connector;
