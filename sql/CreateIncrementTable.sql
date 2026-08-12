--
-- Tables for the IDProvider extension
--

-- Notes table
CREATE TABLE /*_*/idprovider_increments
(

    -- Unique ID to identify the prefix (namespace)
    pid       int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

    -- Increment prefix name
    prefix    varbinary(255) NOT NULL DEFAULT '',

    -- Current increment number
    increment int unsigned NOT NULL default 0

    -- Note value as a string.

) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/idprovider_increments_prefix ON /*_*/idprovider_increments (prefix);
