CREATE TABLE IF NOT EXISTS `#__focalpoint_legends` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(150) NOT NULL,
  `alias` varchar(250) NOT NULL,
  `subtitle` varchar(300) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

CREATE TABLE IF NOT EXISTS `#__focalpoint_locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `map_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `othertypes` varchar(250) NOT NULL,
  `alias` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `fulldescription` text NOT NULL,
  `image` text NOT NULL,
  `linktype` tinyint(1) NOT NULL DEFAULT '0',
  `altlink` varchar(300) NOT NULL,
  `maplinkid` int(11) NOT NULL,
  `menulink` varchar(300) NOT NULL,
  `address` varchar(300) NOT NULL,
  `geoaddress` varchar(300) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `marker` varchar(255) NOT NULL,
  `keylocation` varchar(255) NOT NULL,
  `includesubcats` varchar(255) NOT NULL,
  `customfieldsdata` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `params` varchar(255) NOT NULL,
  `metadata` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;

CREATE TABLE IF NOT EXISTS `#__focalpoint_locationtypes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(150) NOT NULL,
  `alias` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `marker` varchar(255) NOT NULL,
  `legend` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `customfields` text NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

CREATE TABLE IF NOT EXISTS `#__focalpoint_location_type_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `locationtype_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

CREATE TABLE IF NOT EXISTS `#__focalpoint_maps` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(150) NOT NULL,
  `alias` varchar(250) NOT NULL,
  `text` text NOT NULL,
  `tabsdata` text NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `centerpoint` varchar(300) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL,
  `kmlfile` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `metadata` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;