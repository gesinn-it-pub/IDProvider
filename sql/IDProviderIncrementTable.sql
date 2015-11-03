--
-- Tables for the GetId extension
--

-- Notes table
CREATE TABLE /*_*/idprovider_increments (

  -- Unique ID to identify the prefix (namespace)
  pid int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Increment prefix name
  prefix varchar(1024) NOT NULL,

  -- Current increment number
  increment int unsigned NOT NULL default 0

  -- Note value as a string.

) /*$wgDBTableOptions*/;
