-- Permissions --

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('documents', 'can_admin', 'Amministrazione del modulo documenti', 'Inserimento modifica ed eliminazione di documenti e categorie', 1),
('documents', 'can_view_private', 'Visualizzazione documenti privati', 'Visualizzazione di documenti inseriti con flag privato a vero', 0);

--
-- Table structure for table `documents_category`
--

CREATE TABLE IF NOT EXISTS `documents_category` (
`id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(6) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `documents_document`
--

CREATE TABLE IF NOT EXISTS `documents_document` (
`id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` int(8) NOT NULL,
  `description` text,
  `private` tinyint(1) NOT NULL,
  `insertion_date` datetime NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `documents_item_category`
--

CREATE TABLE IF NOT EXISTS `documents_document_category` (
`id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents_category`
--
ALTER TABLE `documents_category`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents_document`
--
ALTER TABLE `documents_document`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents_document_category`
--
ALTER TABLE `documents_document_category`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents_category`
--
ALTER TABLE `documents_category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `documents_document`
--
ALTER TABLE `documents_document`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `documents_document_category`
--
ALTER TABLE `documents_document_category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
