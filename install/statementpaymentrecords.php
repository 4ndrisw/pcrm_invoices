<?php defined('BASEPATH') or exit('No direct script access allowed');


if (!$CI->db->table_exists(db_prefix() . 'statementpaymentrecords')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "statementpaymentrecords` (
      `id` int NOT NULL,
      `statementid` int NOT NULL,
      `amount` decimal(15,2) NOT NULL,
      `paymentmode` varchar(40) DEFAULT NULL,
      `paymentmethod` varchar(191) DEFAULT NULL,
      `date` date NOT NULL,
      `daterecorded` datetime NOT NULL,
      `note` text NOT NULL,
      `transactionid` mediumtext
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'statementpaymentrecords`
      ADD PRIMARY KEY (`id`),
      ADD KEY `invoiceid` (`invoiceid`),
      ADD KEY `paymentmethod` (`paymentmethod`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'statementpaymentrecords`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}