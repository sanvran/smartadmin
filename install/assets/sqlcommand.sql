-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2022 at 04:21 AM
-- Server version: 10.5.13-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ekart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `forgot_password_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `role`, `permissions`, `created_by`, `forgot_password_code`, `fcm_id`, `date_created`) VALUES
(1,	'admin',	'0192023a7bbd73250516f069df18b500',	'info@ecart.in',	'super admin',	'{\"orders\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"categories\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"subcategories\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"products\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"products_order\":{\"read\":\"1\",\"update\":\"1\"},\"featured\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"customers\":{\"read\":\"1\"},\"payment\":{\"read\":\"1\",\"update\":\"1\"},\"notifications\":{\"create\":\"1\",\"read\":\"1\",\"delete\":\"1\"},\"transactions\":{\"read\":\"1\"},\"settings\":{\"read\":\"1\",\"update\":\"1\"},\"locations\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"reports\":{\"create\":\"1\",\"read\":\"1\"},\"faqs\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"home_sliders\":{\"create\":\"1\",\"read\":\"1\",\"delete\":\"1\"},\"new_offers\":{\"create\":\"1\",\"read\":\"1\",\"delete\":\"1\"},\"promo_codes\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"delivery_boys\":{\"create\":\"1\",\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"},\"return_requests\":{\"read\":\"1\",\"update\":\"1\",\"delete\":\"1\"}}',	0,	'1beaf755d5b73d40bfb300722286235c',	'dF4YPg3pQvKzr04ucBNKq5:APA91bHfvSnuaHMWs-wQwC1KsfWNlr5pABsKwlQun3OWEz2QJuaXw11eA_Cv2YxH5xOcTtk-lOgufBx2kiUC7Vlys3v3q3K1VXpPtF5SBSirZvyfvq6Dy4MtuRw3opFWvM8K7jXFY7phhj',	'2020-06-22 16:48:25');

-- --------------------------------------------------------

--
-- Table structure for table `area`
--

CREATE TABLE `area` (
  `id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_id` int(11) NOT NULL,
  `minimum_free_delivery_order_amount` int(20) NOT NULL DEFAULT 0,
  `minimum_order_amount` int(50) NOT NULL DEFAULT 0,
  `delivery_charges` int(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(2) DEFAULT 1,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `slug` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(2) NOT NULL DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `save_for_later` tinyint(4) NOT NULL DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `row_order` int(11) NOT NULL DEFAULT 0,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) DEFAULT 1,
  `web_image` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `id` int(11) NOT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_boys`
--

CREATE TABLE `delivery_boys` (
  `id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_note` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `bonus` int(11) NOT NULL,
  `bonus_method` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` double DEFAULT 0,
  `driving_license` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_identity_card` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `bank_account_number` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ifsc_code` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_payment_information` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `is_available` tinyint(2) NOT NULL DEFAULT 1,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `fcm_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cash_received` double NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_boy_notifications`
--

CREATE TABLE `delivery_boy_notifications` (
  `id` int(11) NOT NULL,
  `delivery_boy_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `title` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(56) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fcm_id` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` char(1) COLLATE utf8mb4_unicode_ci DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flash_sales`
--

CREATE TABLE `flash_sales` (
  `id` int(11) NOT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flash_sales_products`
--

CREATE TABLE `flash_sales_products` (
  `id` int(11) NOT NULL,
  `flash_sales_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `discounted_price` float NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fund_transfers`
--

CREATE TABLE `fund_transfers` (
  `id` int(11) NOT NULL,
  `delivery_boy_id` int(11) NOT NULL,
  `type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'credit | debit',
  `opening_balance` double NOT NULL,
  `closing_balance` double NOT NULL,
  `amount` double NOT NULL,
  `status` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `order_id` int(11) NOT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_date` datetime NOT NULL,
  `phone_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_list` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_sale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_charge` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_directory` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` int(11) NOT NULL,
  `image` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `type` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section_position` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivery_boy_id` int(11) DEFAULT 0,
  `otp` int(10) DEFAULT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_note` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` float NOT NULL,
  `delivery_charge` float NOT NULL,
  `tax_amount` float NOT NULL DEFAULT 0,
  `tax_percentage` float NOT NULL DEFAULT 0,
  `wallet_balance` float NOT NULL,
  `discount` float NOT NULL,
  `promo_code` varchar(28) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promo_discount` float NOT NULL DEFAULT 0,
  `final_total` float DEFAULT NULL,
  `payment_method` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_time` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seller_notes` text CHARACTER SET utf8 DEFAULT NULL,
  `local_pickup` tinyint(2) NOT NULL DEFAULT 0,
  `pickup_time` datetime DEFAULT NULL,
  `status` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_from` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_bank_transfers`
--

CREATE TABLE `order_bank_transfers` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `attachment` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT 0,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_variant_id` int(11) NOT NULL,
  `product_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` float NOT NULL,
  `discounted_price` double NOT NULL,
  `tax_amount` float DEFAULT 0,
  `tax_percentage` float DEFAULT 0,
  `discount` float NOT NULL,
  `sub_total` float NOT NULL,
  `deliver_by` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(6) NOT NULL,
  `txnid` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_amount` decimal(7,2) NOT NULL,
  `payment_status` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `itemid` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdtime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_type` varchar(56) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_address` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_requested` int(11) NOT NULL,
  `remarks` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `row_order` int(11) NOT NULL DEFAULT 0,
  `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_id` tinyint(4) DEFAULT 0,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `indicator` tinyint(4) DEFAULT 0 COMMENT '0 - none | 1 - veg | 2 - non-veg',
  `manufacturer` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `made_in` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_status` tinyint(4) DEFAULT NULL,
  `cancelable_status` tinyint(4) DEFAULT NULL,
  `till_status` varchar(28) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `other_images` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_chart` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_delivery` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(2) DEFAULT 1,
  `ratings` float(2,1) DEFAULT 0.0,
  `number_of_ratings` int(11) DEFAULT 0,
  `is_cod_allowed` tinyint(2) NOT NULL DEFAULT 1,
  `total_allowed_quantity` int(11) NOT NULL DEFAULT 0,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_ads`
--

CREATE TABLE `product_ads` (
  `id` int(11) NOT NULL,
  `ad1` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ad2` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ad3` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  `review` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(2) NOT NULL DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

CREATE TABLE `product_variant` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `measurement` float NOT NULL,
  `measurement_unit_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `discounted_price` float NOT NULL,
  `serve_for` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock` float NOT NULL,
  `stock_unit_id` int(11) NOT NULL,
  `images` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `promo_code` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_date` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_of_users` int(11) NOT NULL,
  `minimum_order_amount` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  `discount_type` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_discount_amount` int(11) NOT NULL,
  `repeat_usage` tinyint(4) NOT NULL,
  `no_of_repeat_usage` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `remarks` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `row_order` int(11) NOT NULL DEFAULT 0,
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `style` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_type` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_ids` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_ids` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `id` int(11) NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  `account_details` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gst_no` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pan_no` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commission` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` int(11) NOT NULL,
  `last_login_ip` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `variable` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `variable`, `value`) VALUES
(6, 'logo', 'logo.png'),
(9, 'privacy_policy', '<p><strong>Privacy Policy</strong></p>\r\n\r\n<p> </p>'),
(10, 'terms_conditions', '<p><strong>Terms &amp; Conditions</strong></p>\r\n\r\n<p> </p>'),
(11, 'fcm_server_key', 'AAAAROTG9TU:APA91bGfGd9HNUR-woMSmMS3wi6YfXE-vL7yrqsTm62txgvpXiX-9DqUMY94L_kZDHKnELRu9JUKlc4hCVaFrU1jFVFEfZSH1X-coAy2VaQ_i4TEKNLLDYXD-PsifV4dYidacapxhOxp'),
(12, 'contact_us', '<h2>E-commerce'),
(13, 'system_timezone', '{\"system_configurations\":\"1\",\"system_timezone_gmt\":\"+05:30\",\"system_configurations_id\":\"13\",\"app_name\":\"eCart\",\"support_number\":\"+91 9876543210\",\"support_email\":\"support@ekart.com\",\"current_version\":\"1.0.0\",\"minimum_version_required\":\"1.0.0\",\"is-version-system-on\":\"0\",\"store_address\":\"#262-263, Time Square Empire, SH 42 Mirjapar highway, Bhuj - Kutch 370001, Gujarat India.\",\"map_latitude\":\"23.23305215147397\",\"map_longitude\":\"69.64400665873588\",\"currency\":\"u20b9\",\"system_timezone\":\"Asia/Kolkata\",\"max_cart_items_count\":\"30\",\"generate-otp\":\"1\",\"area-wise-delivery-charge\":\"1\",\"min_amount\":\"2000\",\"min_order_amount\":\"50\",\"delivery_charge\":\"1000\",\"is-refer-earn-on\":\"1\",\"min-refer-earn-order-amount\":\"100\",\"refer-earn-bonus\":\"2\",\"refer-earn-method\":\"percentage\",\"max-refer-earn-amount\":\"5000\",\"minimum-withdrawal-amount\":\"500\",\"max-product-return-days\":\"10\",\"low-stock-limit\":\"10\",\"user-wallet-refill-limit\":\"100000\",\"tax_name\":\"GST\",\"tax_number\":\"0123456789\",\"delivery-boy-bonus-method\":\"rupees\",\"delivery-boy-bonus-percentage\":\"10\",\"under_maintenance_system\":\"0\",\"ratings\":\"1\",\"local-pickup\":\"1\",\"support_system\":\"1\",\"welcome-wallet-balance\":\"1\",\"wallet_balance\":\"500\",\"from_mail\":\"info@ecart.com\",\"reply_to\":\"info@ecart.com\",\"smtp-from-mail\":\"\",\"smtp-reply-to\":\"\",\"smtp-email-password\":\"\",\"smtp-host\":\"\",\"smtp-port\":\"\",\"smtp-content-type\":\"\",\"smtp-encryption-type\":\"\"}'),
(15, 'about_us', '<h2>About Us</h2>\r\n\r\n<p>eCart is one of the most selling and trending&nbsp; Grocery, Food Delivery, Fruits &amp; Vegetable store, Full Android eCommerce &amp; Website. which is helps to create your own app and web with your brand name.</p>\r\n\r\n<p>eCart has creative and dedicated group of developers who are mastered in Apps Developments and Web Development with a nice in delivering quality solutions to customers across the globe.</p>\r\n\r\n<p>Everything there including code, doc, amazing support, and most important developed by WRTeam.</p>'),
(80, 'currency', '₹'),
(81, 'delivery_boy_privacy_policy', '<p><strong>Privacy Policy</strong></p>\r\n\r\n<p>WRTeam built the eCart Delivery Boy app as a Free app. This SERVICE is provided by WRTeam at no cost and is intended for use as is.</p>\r\n\r\n<p>This page is used to inform visitors regarding our policies with the collection, use, and disclosure of Personal Information if anyone decided to use our Service.</p>\r\n\r\n<p>If you choose to use our Service, then you agree to the collection and use of information in relation to this policy. The Personal Information that we collect is used for providing and improving the Service. We will not use or share your information with anyone except as described in this Privacy Policy.</p>\r\n\r\n<p>The terms used in this Privacy Policy have the same meanings as in our Terms and Conditions, which is accessible at eCart Delivery Boy unless otherwise defined in this Privacy Policy.</p>\r\n\r\n<p><strong>Information Collection and Use</strong></p>\r\n\r\n<p>For a better experience, while using our Service, we may require you to provide us with certain personally identifiable information. The information that we request will be retained by us and used as described in this privacy policy.</p>\r\n\r\n<p>The app does use third party services that may collect information used to identify you.</p>\r\n\r\n<p>Link to privacy policy of third party service providers used by the app</p>\r\n\r\n<ul>\r\n	<li><a href=\"https://www.google.com/policies/privacy/\" target=\"_blank\">Google Play Services</a></li>\r\n	<li><a href=\"https://firebase.google.com/support/privacy/\" target=\"_blank\">Firebase Crashlytics</a></li>\r\n</ul>\r\n\r\n<p><strong>Log Data</strong></p>\r\n\r\n<p>We want to inform you that whenever you use our Service, in a case of an error in the app we collect data and information (through third-party products) on your phone called Log Data. This Log Data may include information such as your device Internet Protocol (“IP”) address, device name, operating system version, the configuration of the app when utilizing our Service, the time and date of your use of the Service, and other statistics.</p>\r\n\r\n<p><strong>Cookies</strong></p>\r\n\r\n<p>Cookies are files with a small amount of data that are commonly used as anonymous unique identifiers. These are sent to your browser from the websites that you visit and are stored on your device&#39;s internal memory.</p>\r\n\r\n<p>This Service does not use these “cookies” explicitly. However, the app may use third-party code and libraries that use “cookies” to collect information and improve their services. You have the option to either accept or refuse these cookies and know when a cookie is being sent to your device. If you choose to refuse our cookies, you may not be able to use some portions of this Service.</p>\r\n\r\n<p><strong>Service Providers</strong></p>\r\n\r\n<p>We may employ third-party companies and individuals due to the following reasons:</p>\r\n\r\n<ul>\r\n	<li>To facilitate our Service;</li>\r\n	<li>To provide the Service on our behalf;</li>\r\n	<li>To perform Service-related services; or</li>\r\n	<li>To assist us in analyzing how our Service is used.</li>\r\n</ul>\r\n\r\n<p>We want to inform users of this Service that these third parties have access to their Personal Information. The reason is to perform the tasks assigned to them on our behalf. However, they are obligated not to disclose or use the information for any other purpose.</p>\r\n\r\n<p><strong>Security</strong></p>\r\n\r\n<p>We value your trust in providing us with your Personal Information, thus we are striving to use commercially acceptable means of protecting it. But remember that no method of transmission over the internet, or method of electronic storage is 100% secure and reliable, and we cannot guarantee its absolute security.</p>\r\n\r\n<p><strong>Links to Other Sites</strong></p>\r\n\r\n<p>This Service may contain links to other sites. If you click on a third-party link, you will be directed to that site. Note that these external sites are not operated by us. Therefore, we strongly advise you to review the Privacy Policy of these websites. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services.</p>\r\n\r\n<p><strong>Children’s Privacy</strong></p>\r\n\r\n<p>These Services do not address anyone under the age of 13. We do not knowingly collect personally identifiable information from children under 13 years of age. In the case we discover that a child under 13 has provided us with personal information, we immediately delete this from our servers. If you are a parent or guardian and you are aware that your child has provided us with personal information, please contact us so that we will be able to do necessary actions.</p>\r\n\r\n<p><strong>Changes to This Privacy Policy</strong></p>\r\n\r\n<p>We may update our Privacy Policy from time to time. Thus, you are advised to review this page periodically for any changes. We will notify you of any changes by posting the new Privacy Policy on this page.</p>\r\n\r\n<p>This policy is effective as of 2021-10-06</p>\r\n\r\n<p><strong>Contact Us</strong></p>\r\n\r\n<p>If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at info@wrteam.in.</p>'),
(82, 'delivery_boy_terms_conditions', '<p><strong>Terms &amp; Conditions</strong></p>\r\n\r\n<p>By downloading or using the app, these terms will automatically apply to you – you should make sure therefore that you read them carefully before using the app. You’re not allowed to copy or modify the app, any part of the app, or our trademarks in any way. You’re not allowed to attempt to extract the source code of the app, and you also shouldn’t try to translate the app into other languages or make derivative versions. The app itself, and all the trademarks, copyright, database rights, and other intellectual property rights related to it, still belong to WRTeam.</p>\r\n\r\n<p>WRTeam is committed to ensuring that the app is as useful and efficient as possible. For that reason, we reserve the right to make changes to the app or to charge for its services, at any time and for any reason. We will never charge you for the app or its services without making it very clear to you exactly what you’re paying for.</p>\r\n\r\n<p>The eCart Delivery Boy app stores and processes personal data that you have provided to us, in order to provide our Service. It’s your responsibility to keep your phone and access to the app secure. We, therefore, recommend that you do not jailbreak or root your phone, which is the process of removing software restrictions and limitations imposed by the official operating system of your device. It could make your phone vulnerable to malware/viruses/malicious programs, compromise your phone’s security features and it could mean that the eCart Delivery Boy app won’t work properly or at all.</p>\r\n\r\n<p>The app does use third-party services that declare their own Terms and Conditions.</p>\r\n\r\n<p>Link to Terms and Conditions of third party service providers used by the app</p>\r\n\r\n<ul>\r\n	<li><a href=\"https://policies.google.com/terms\" target=\"_blank\">Google Play Services</a></li>\r\n	<li><a href=\"https://firebase.google.com/terms/crashlytics\" target=\"_blank\">Firebase Crashlytics</a></li>\r\n</ul>\r\n\r\n<p>You should be aware that there are certain things that WRTeam will not take responsibility for. Certain functions of the app will require the app to have an active internet connection. The connection can be Wi-Fi, or provided by your mobile network provider, but WRTeam cannot take responsibility for the app not working at full functionality if you don’t have access to Wi-Fi, and you don’t have any of your data allowance left.</p>\r\n\r\n<p> </p>\r\n\r\n<p>If you’re using the app outside of an area with Wi-Fi, you should remember that the terms of the agreement with your mobile network provider will still apply. As a result, you may be charged by your mobile provider for the cost of data for the duration of the connection while accessing the app, or other third-party charges. In using the app, you’re accepting responsibility for any such charges, including roaming data charges if you use the app outside of your home territory (i.e. region or country) without turning off data roaming. If you are not the bill payer for the device on which you’re using the app, please be aware that we assume that you have received permission from the bill payer for using the app.</p>\r\n\r\n<p>Along the same lines, WRTeam cannot always take responsibility for the way you use the app i.e. You need to make sure that your device stays charged – if it runs out of battery and you can’t turn it on to avail the Service, WRTeam cannot accept responsibility.</p>\r\n\r\n<p>With respect to WRTeam’s responsibility for your use of the app, when you’re using the app, it’s important to bear in mind that although we endeavour to ensure that it is updated and correct at all times, we do rely on third parties to provide information to us so that we can make it available to you. WRTeam accepts no liability for any loss, direct or indirect, you experience as a result of relying wholly on this functionality of the app.</p>\r\n\r\n<p>At some point, we may wish to update the app. The app is currently available on Android – the requirements for system(and for any additional systems we decide to extend the availability of the app to) may change, and you’ll need to download the updates if you want to keep using the app. WRTeam does not promise that it will always update the app so that it is relevant to you and/or works with the Android version that you have installed on your device. However, you promise to always accept updates to the application when offered to you, We may also wish to stop providing the app, and may terminate use of it at any time without giving notice of termination to you. Unless we tell you otherwise, upon any termination, (a) the rights and licenses granted to you in these terms will end; (b) you must stop using the app, and (if needed) delete it from your device.</p>\r\n\r\n<p><strong>Changes to This Terms and Conditions</strong></p>\r\n\r\n<p>We may update our Terms and Conditions from time to time. Thus, you are advised to review this page periodically for any changes. We will notify you of any changes by posting the new Terms and Conditions on this page.</p>\r\n\r\n<p>These terms and conditions are effective as of 2021-10-06</p>\r\n\r\n<p><strong>Contact Us</strong></p>\r\n\r\n<p>If you have any questions or suggestions about our Terms and Conditions, do not hesitate to contact us at info@wrteam.in.</p>'),
(92,	'payment_methods',	'{\"payment_method_settings\":\"1\",\"cod_payment_method\":\"1\",\"cod_mode\":\"product\",\"paypal_payment_method\":\"1\",\"paypal_mode\":\"sandbox\",\"paypal_currency_code\":\"USD\",\"paypal_business_email\":\"seller@somedomain.com\",\"payumoney_payment_method\":\"1\",\"payumoney_mode\":\"sandbox\",\"payumoney_merchant_key\":\"Q9zIXtyrghco\",\"payumoney_merchant_id\":\"7401345671255\",\"payumoney_salt\":\"u312deetgdODEvwdy\",\"razorpay_payment_method\":\"1\",\"razorpay_key\":\"rzp_test_wNjW8AREdgsdgkghdXStP\",\"razorpay_secret_key\":\"RSGgbQVghmsFbpZTOMXgddi4v414Fh\",\"paystack_payment_method\":\"1\",\"paystack_public_key\":\"pk_test_fd8f8d9cdf548cbd14g3c83a4bdf6cdee5c11fdd8e656c66\",\"paystack_secret_key\":\"sk_test_dcc02e9379745953bb9fh33b6d4c0dec928fh1f7e0118\",\"flutterwave_payment_method\":\"1\",\"flutterwave_public_key\":\"FLWPUBK_TEST-1fferbaed6eefdf3788cd2bcbb898d3b90c59-X\",\"flutterwave_secret_key\":\"FLWSECK_TEST-c659ffd7fertd346304fff90fc4b67ae735b126-X\",\"flutterwave_encryption_key\":\"FLWSECK_TEST25cyrtyrt36eddfcfcaa\",\"flutterwave_currency_code\":\"KES\",\"midtrans_payment_method\":\"1\",\"is_production\":\"0\",\"midtrans_merchant_id\":\"G28236yry567496\",\"midtrans_client_key\":\"SB-Mid-client-roooHdfgdgrl0UI6xDJ7-\",\"midtrans_server_key\":\"SB-Mid-server-3SdfMP6fsXVmklsN2sRGrbffGUfdal\",\"stripe_payment_method\":\"1\",\"stripe_publishable_key\":\"pk_test_51Hh90WLYfObhNTTfawooBHwynrlerfiPo2uwxyCVqGNNCWGmpdOHuaW4rYS9cDldKJ1hxV5ik52UXUDSYgEM66sdgsOX45550065US7tRX\",\"stripe_secret_key\":\"sk_test_51Hh90WLYfObdfhNgdsgsTTwO8kCsbdnMdmLxiGHEpiQPGBkYlahlBAQ3RnXPIKGn3YsGIEMoIQ5bNfxye4kzE6wfLiINzNk00xOYprnZt\",\"stripe_webhook_secret_key\":\"whsec_c9XIsxcWEweL0J3DNtDH812YEBfasfazcsVw8Ol\",\"stripe_currency_code\":\"INR\",\"paytm_payment_method\":\"1\",\"paytm_mode\":\"sandbox\",\"paytm_merchant_key\":\"eIcrB!gsfsdfsgts\",\"paytm_merchant_id\":\"PpGeMd34548dfa4534z49525540215\",\"ssl_method\":\"1\",\"ssl_mode\":\"sandbox\",\"ssl_store_id\":\"wrtea61faaf0e0212f7c6177\",\"ssl_store_password\":\"wrtea610e1202f7cdfdsf6177@ssl\",\"direct_bank_transfer_method\":\"1\",\"account_name\":\"eCart Customer App\",\"account_number\":\"98765432651098765\",\"bank_name\":\"eCart Bank\",\"bank_code\":\"ECART12345\",\"notes\":\"Hello, you need to pay in the bank first to accept an order from our site, then you need to send an image of the receipt from the order detail page, After the confirmation of the image you will get an update either accept or reject based on the receipt.\"}'),
(83, 'time_slot_config', '{\"time_slot_config\":\"1\",\"is_time_slots_enabled\":\"1\",\"delivery_starts_from\":\"2\",\"allowed_days\":\"30\"}'),
(95, 'manager_app_privacy_policy', '<p><strong>Privacy Policy</strong></p>\r\n\r\n<p>'),
(96, 'manager_app_terms_conditions', '<p><strong>Terms &amp; Conditions</strong></p>\r\n\r\n<p>'),
(101, 'refund_policy', '<p>Refund Policy</p>'),
(102, 'shipping_policy', '<p>Shipping Policies</p>'),
(103, 'delivery_returns_policy', '<p>Delivery &amp; Return Policies</p>'),
(99, 'categories_settings', '{\"add_category_settings\":\"1\",\"cat_style\":\"style_2\",\"max_visible_categories\":\"6\",\"max_col_in_single_row\":\"3\"}'),
(97, 'front_end_settings', '{\"front_end_settings\":\"1\",\"android_app_url\":\"https://play.google.com\",\"call_back_url\":\"http://ekart.local:8000/\",\"common_meta_keywords\":\"eCart,WebeCart,eCart Front,eCart Web,eCart Front End\",\"common_meta_description\":\"eCart Front End is Web Version of eCart - Grocery, Food Delivery, Fruits & Vegetable store.\",\"color\":\"#83b735\",\"favicon\":\"1610961661.239.png\",\"web_logo\":\"1617174220.3855.png\",\"loading\":\"1632828376.1337.gif\",\"screenshots\":\"1608552564.1753.png\",\"google_play\":\"1608552564.1758.png\"}'),
(100, 'front_end_about_us', '<h2>About Us</h2>\r\n\r\n<p>eCart is one of the most selling and trending');

-- --------------------------------------------------------

--
-- Table structure for table `slider`
--

CREATE TABLE `slider` (
  `id` int(11) NOT NULL,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image2` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slider_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media`
--

CREATE TABLE `social_media` (
  `id` int(11) NOT NULL,
  `icon` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategory`
--

CREATE TABLE `subcategory` (
  `id` int(11) NOT NULL,
  `row_order` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int(11) NOT NULL,
  `title` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','opened','resolved','closed','reopen') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `type` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_type`
--

CREATE TABLE `ticket_type` (
  `id` int(11) NOT NULL,
  `type` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_time` time NOT NULL,
  `to_time` time NOT NULL,
  `last_order_time` time NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `txn_id` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payu_txn_id` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double NOT NULL,
  `status` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` datetime NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `id` int(11) NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_code` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `conversion` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`id`, `name`, `short_code`, `parent_id`, `conversion`) VALUES
(1, ' Kilo Gram', 'kg', NULL, NULL),
(2, ' Gram', 'gm', 1, 1000),
(3, 'Liter', 'ltr', NULL, NULL),
(4, 'Milliliter', 'ml', 3, 1000),
(5, 'Pack', 'pack', NULL, NULL),
(6, 'Piece', 'pc', NULL, NULL),
(7, 'Meter', 'm', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

CREATE TABLE `updates` (
  `id` int(11) NOT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `updates`
--

INSERT INTO `updates` (`id`, `version`) VALUES
(1, 'v2.0.8'),
(2, 'v2.0.8.1'),
(3, 'v2.0.8.2'),
(4, 'v2.0.9'),
(5, 'v2.0.9.1'),
(6, 'v2.0.9.2'),
(7, 'v2.0.10'),
(8, 'v2.1.0'),
(9, 'v2.0.11'),
(10, 'v2.0.11.1'),
(11, 'v2.0.12'),
(12, 'v2.0.13'),
(13, 'v3.0.0'),
(14, 'v3.0.1'),
(18, 'v3.0.2'),
(19, 'v3.0.3'),
(20, 'v3.0.4'),
(21, 'v3.0.5'),
(22, 'v3.0.6'),
(23, 'v4.0.0'),
(24, 'v4.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '91',
  `mobile` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apikey` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` double NOT NULL DEFAULT 0,
  `referral_code` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `friends_code` varchar(28) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_id` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternate_mobile` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmark` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `pincode` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(56) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(56) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `order_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'credit | debit',
  `amount` double NOT NULL,
  `message` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_requests`
--

CREATE TABLE `withdrawal_requests` (
  `id` int(11) NOT NULL,
  `type` varchar(28) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `message` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_boys`
--
ALTER TABLE `delivery_boys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_boy_notifications`
--
ALTER TABLE `delivery_boy_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_boy_id` (`delivery_boy_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `flash_sales`
--
ALTER TABLE `flash_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flash_sales_products`
--
ALTER TABLE `flash_sales_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flash_sales_id` (`flash_sales_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Indexes for table `fund_transfers`
--
ALTER TABLE `fund_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_boy_id` (`delivery_boy_id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `delivery_boy_id` (`delivery_boy_id`);

--
-- Indexes for table `order_bank_transfers`
--
ALTER TABLE `order_bank_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_id` (`tax_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `product_ads`
--
ALTER TABLE `product_ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `measurement_unit_id` (`measurement_unit_id`),
  ADD KEY `stock_unit_id` (`stock_unit_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_variant_id` (`product_variant_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_ids` (`product_ids`(250)),
  ADD KEY `category_ids` (`category_ids`(250));

--
-- Indexes for table `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `social_media`
--
ALTER TABLE `social_media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategory`
--
ALTER TABLE `subcategory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ticket_type`
--
ALTER TABLE `ticket_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `area`
--
ALTER TABLE `area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_boys`
--
ALTER TABLE `delivery_boys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_boy_notifications`
--
ALTER TABLE `delivery_boy_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flash_sales`
--
ALTER TABLE `flash_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flash_sales_products`
--
ALTER TABLE `flash_sales_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fund_transfers`
--
ALTER TABLE `fund_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_bank_transfers`
--
ALTER TABLE `order_bank_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_requests`
--
ALTER TABLE `payment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_ads`
--
ALTER TABLE `product_ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variant`
--
ALTER TABLE `product_variant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `slider`
--
ALTER TABLE `slider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media`
--
ALTER TABLE `social_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategory`
--
ALTER TABLE `subcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_type`
--
ALTER TABLE `ticket_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
