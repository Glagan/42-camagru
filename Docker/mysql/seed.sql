-- Adminer 4.8.0 MySQL 8.0.23 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

INSERT INTO `users` (`id`, `username`, `email`, `password`, `verified`, `receiveComments`) VALUES
(1,	'glagan',	'glagan@protonmail.com',	'$2y$10$44DLrdeD.03z4eiQyuyPAOXTnPIcdRStrEX7FmEtzPvc.wCu9UlSi',	1,	0),
(2,	'Anonymous',	'anon@mail.com',	'$2y$10$HS145gYyvu7XQVaYv.Gg../nkanMW8bjKsX98JHMiz2lujuqNsRMS',	1,	1),
(3,	'Martin',	'martin@gmail.com',	'$2y$10$h7dFlSJf7ZhbZ1aYU3Yfouqe54Nzrawu98MOq591aFZTnIYRy71Rm',	1,	1),
(4,	'Eva',	'eva.who@protonmail.ch',	'$2y$10$.oSQonligmFwa1X1kpioWezZ9YIRgGpXV2SkiHuM9Vzbhgut530N6',	1,	1);

INSERT INTO `creations` (`id`, `user`, `name`, `animated`, `private`, `at`) VALUES
(1,		1,	'155146_70b2e33292.webm',	1,	0,	'2021-04-12 15:52:04'),
(2,		2,	'155316_b6b0ff339f.png',	0,	0,	'2021-04-12 15:53:17'),
(3,		1,	'155510_47a3c95e21.webm',	1,	0,	'2021-04-12 15:55:12'),
(4,		1,	'155607_cebaf9f25d.png',	0,	0,	'2021-04-12 15:56:07'),
(5,		3,	'155718_f1bcf65470.webm',	1,	0,	'2021-04-12 15:57:20'),
(6,		1,	'155824_62c66cce01.png',	0,	0,	'2021-04-12 15:58:25'),
(7,		1,	'160000_16572455a1.webm',	1,	0,	'2021-04-12 16:00:04'),
(8,		2,	'160042_da41b8296a.png',	0,	0,	'2021-04-12 16:00:42'),
(9,		1,	'160126_2836d161df.webm',	1,	0,	'2021-04-12 16:01:35'),
(10,	1,	'175956_b7da172af9.webm',	1,	0,	'2021-04-12 17:59:59'),
(11,	2,	'180346_3e033260d9.png',	0,	0,	'2021-04-12 18:03:47'),
(12,	1,	'180654_d82712205b.png',	0,	0,	'2021-04-12 18:06:55'),
(13,	3,	'180940_6576924806.png',	0,	0,	'2021-04-12 18:09:41'),
(14,	1,	'182750_b0236a1ece.png',	0,	0,	'2021-04-12 18:27:51'),
(15,	3,	'183015_d1282064ce.png',	0,	0,	'2021-04-12 18:30:16'),
(16,	1,	'183749_80fcd6108b.png',	0,	0,	'2021-04-12 18:37:49'),
(17,	2,	'183933_2b243f1f4b.png',	0,	0,	'2021-04-12 18:39:33'),
(18,	1,	'184108_913e6969bc.png',	0,	0,	'2021-04-12 18:41:08'),
(19,	3,	'184119_8cecfd3b90.png',	0,	0,	'2021-04-12 18:41:19'),
(20,	2,	'184159_4dd95dea22.png',	0,	0,	'2021-04-12 18:41:59'),
(21,	1,	'184242_0933c85c4d.png',	0,	0,	'2021-04-12 18:42:42'),
(22,	3,	'184348_8e3c71c905.png',	0,	0,	'2021-04-12 18:43:50'),
(23,	2,	'184422_d419e3902b.webm',	1,	0,	'2021-04-12 18:44:27'),
(24,	3,	'184504_d3df02c38c.webm',	1,	0,	'2021-04-12 18:45:14'),
(25,	1,	'184537_53a5598f74.png',	0,	0,	'2021-04-12 18:45:38'),
(26,	1,	'184634_52cf6f8506.png',	0,	0,	'2021-04-12 18:46:35'),
(27,	1,	'195626_f57e9db4ee.webm',	1,	0,	'2021-04-12 19:56:28');

INSERT INTO `comments` (`id`, `image`, `user`, `message`, `at`, `edited`, `deleted`) VALUES
(1,		1,	4,	'alienPls',	'2021-04-12 19:19:00',	NULL,	0),
(2,		7,	4,	'What anime is this ?',	'2021-04-12 19:19:28',	NULL,	0),
(3,		10,	4,	'I don\'t even know what this means.',	'2021-04-12 19:19:45',	NULL,	0),
(4,		9,	4,	'??',	'2021-04-12 19:19:51',	NULL,	0),
(5,		6,	4,	'SSSsss',	'2021-04-12 19:21:01',	NULL,	0),
(6,		11,	4,	'I wish I was there...',	'2021-04-12 19:21:20',	NULL,	0),
(7,		13,	4,	'I think it\'s your worst creation.',	'2021-04-12 19:21:45',	NULL,	0),
(8,		15,	4,	'2min game ? Is that even possible ??',	'2021-04-12 19:21:59',	NULL,	0),
(9,		18,	4,	'He looks so happy !',	'2021-04-12 19:22:29',	NULL,	0),
(10,	21,	4,	'He\'s sooo big !',	'2021-04-12 19:23:10',	NULL,	0),
(11,	22,	4,	'Poor cat :(',	'2021-04-12 19:23:19',	NULL,	0),
(12,	26,	4,	'Jail time baby',	'2021-04-12 19:23:37',	NULL,	0),
(13,	25,	4,	'He\'s so fast !!',	'2021-04-12 19:23:47',	NULL,	0),
(14,	27,	4,	'Isn\'t he cute ? :)',	'2021-04-12 19:56:41',	NULL,	0),
(15,	1,	2,	'Space is so mysterious and beautiful.',	'2021-04-12 20:04:02',	NULL,	0),
(16,	2,	2,	'I can\'t legally say anything about that.',	'2021-04-12 20:04:19',	NULL,	0),
(17,	4,	2,	'I tried but I failed.',	'2021-04-12 20:04:56',	NULL,	0),
(18,	5,	2,	'AAAAAAAAAAaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaahhhhhhhhhhhhhhhh',	'2021-04-12 20:05:06',	NULL,	0),
(19,	6,	2,	'Nice drawing.',	'2021-04-12 20:05:17',	NULL,	0),
(20,	7,	2,	'Is she dead D:',	'2021-04-12 20:05:30',	NULL,	0),
(21,	9,	2,	'I get the reference.',	'2021-04-12 20:05:48',	NULL,	0),
(22,	10,	2,	'Hum, I think it works.',	'2021-04-12 20:06:02',	NULL,	0),
(23,	12,	2,	'Where\'s the cat ?',	'2021-04-12 20:08:29',	NULL,	0),
(24,	14,	2,	'I should learn python.',	'2021-04-12 20:08:50',	NULL,	0),
(25,	16,	2,	'This was a nice game.',	'2021-04-12 20:09:10',	NULL,	0),
(26,	17,	2,	'No @Eva I think *this* is his worst creation.',	'2021-04-12 20:09:38',	NULL,	0),
(27,	19,	2,	'This looks great.',	'2021-04-12 20:10:58',	NULL,	0),
(28,	20,	2,	'Really sneaky, I couldn\'t see them.',	'2021-04-12 20:11:14',	NULL,	0),
(29,	4,	3,	'Try harder then.',	'2021-04-12 20:11:53',	NULL,	0),
(30,	1,	3,	'Indeed it is.',	'2021-04-12 20:12:04',	NULL,	0),
(31,	2,	3,	'Aren\'t you anonymous ?',	'2021-04-12 20:12:18',	NULL,	0),
(32,	5,	3,	'He\'s going super saiyan !!!',	'2021-04-12 20:12:35',	NULL,	0),
(33,	7,	3,	'I think it\'s Yuru Yuri or something like that',	'2021-04-12 20:13:01',	NULL,	0),
(34,	10,	3,	'There is an error.',	'2021-04-12 20:13:15',	NULL,	0),
(35,	10,	3,	'Nevermind I\'m just really bad at coding.',	'2021-04-12 20:13:26',	NULL,	0),
(36,	27,	3,	'Woaf',	'2021-04-12 20:13:49',	NULL,	0),
(37,	27,	3,	'He\'s really cute @Eva',	'2021-04-12 20:13:56',	NULL,	0),
(38,	14,	3,	'<script type=\"application/javascript\">alert(\'Does *this* work ?\');</script>',	'2021-04-12 20:22:52',	NULL,	0),
(39,	14,	3,	'<b>Maybe this ?</b>',	'2021-04-12 20:23:11',	NULL,	0),
(40,	14,	3,	'<div class=\"text-right\">Ok I think it\'s good.</div>',	'2021-04-12 20:23:31',	NULL,	0);

INSERT INTO `likes` (`id`, `user`, `image`, `at`) VALUES
(1,		4,	1,	'2021-04-12 19:19:02'),
(2,		4,	7,	'2021-04-12 19:19:30'),
(3,		4,	15,	'2021-04-12 19:22:09'),
(4,		4,	18,	'2021-04-12 19:22:22'),
(5,		4,	22,	'2021-04-12 19:23:20'),
(6,		4,	26,	'2021-04-12 19:23:38'),
(7,		4,	25,	'2021-04-12 19:23:48'),
(8,		1,	1,	'2021-04-12 19:58:03'),
(9,		1,	3,	'2021-04-12 19:58:08'),
(10,	1,	5,	'2021-04-12 19:58:10'),
(11,	1,	6,	'2021-04-12 19:58:12'),
(12,	1,	8,	'2021-04-12 19:58:14'),
(13,	1,	10,	'2021-04-12 19:58:18'),
(14,	1,	9,	'2021-04-12 19:58:21'),
(15,	1,	15,	'2021-04-12 19:58:28'),
(16,	1,	17,	'2021-04-12 19:58:33'),
(17,	1,	18,	'2021-04-12 19:58:49'),
(18,	1,	19,	'2021-04-12 19:58:53'),
(19,	1,	21,	'2021-04-12 19:58:56'),
(20,	1,	26,	'2021-04-12 19:59:01'),
(21,	1,	25,	'2021-04-12 19:59:03'),
(22,	2,	1,	'2021-04-12 19:59:05'),
(23,	2,	2,	'2021-04-12 19:59:06'),
(24,	2,	3,	'2021-04-12 19:59:08'),
(25,	2,	5,	'2021-04-12 19:59:10'),
(26,	2,	7,	'2021-04-12 19:59:13'),
(27,	2,	8,	'2021-04-12 19:59:15'),
(28,	2,	9,	'2021-04-12 19:59:17'),
(29,	2,	10,	'2021-04-12 19:59:21'),
(30,	2,	11,	'2021-04-12 19:59:23'),
(31,	2,	13,	'2021-04-12 19:59:26'),
(32,	2,	15,	'2021-04-12 19:59:28'),
(33,	2,	17,	'2021-04-12 19:59:32'),
(34,	2,	18,	'2021-04-12 19:59:35'),
(35,	2,	20,	'2021-04-12 19:59:37'),
(36,	2,	22,	'2021-04-12 19:59:39'),
(37,	2,	23,	'2021-04-12 19:59:41'),
(38,	2,	24,	'2021-04-12 19:59:43'),
(39,	2,	25,	'2021-04-12 19:59:45'),
(40,	2,	26,	'2021-04-12 19:59:47'),
(41,	3,	2,	'2021-04-12 19:59:50'),
(42,	3,	3,	'2021-04-12 19:59:51'),
(43,	3,	4,	'2021-04-12 19:59:53'),
(44,	3,	5,	'2021-04-12 19:59:59'),
(45,	3,	6,	'2021-04-12 20:00:04'),
(46,	3,	8,	'2021-04-12 20:00:07'),
(47,	3,	9,	'2021-04-12 20:00:11'),
(48,	3,	12,	'2021-04-12 20:00:16'),
(49,	3,	13,	'2021-04-12 20:00:21'),
(50,	3,	15,	'2021-04-12 20:00:25'),
(51,	3,	16,	'2021-04-12 20:00:29'),
(52,	3,	18,	'2021-04-12 20:02:47'),
(53,	3,	19,	'2021-04-12 20:02:52'),
(54,	3,	20,	'2021-04-12 20:03:01'),
(55,	3,	21,	'2021-04-12 20:03:04'),
(56,	3,	22,	'2021-04-12 20:03:06'),
(57,	3,	23,	'2021-04-12 20:03:10'),
(58,	3,	24,	'2021-04-12 20:03:12'),
(59,	3,	25,	'2021-04-12 20:03:15'),
(60,	3,	26,	'2021-04-12 20:03:19'),
(61,	2,	19,	'2021-04-12 20:10:57'),
(62,	3,	27,	'2021-04-12 20:13:44'),
(63,	3,	17,	'2021-04-12 20:16:51');

INSERT INTO `decorations` (`id`, `name`, `animated`, `public`, `position`) VALUES
(1,		'001.png',	0,	1,	'center-center'),
(2,		'002.png',	0,	1,	'bottom-left'),
(3,		'003.png',	0,	1,	'center-center'),
(4,		'004.png',	0,	1,	'bottom-right'),
(5,		'005.png',	0,	1,	'bottom-left'),
(6,		'006.png',	0,	1,	'top-left'),
(7,		'007.png',	0,	1,	'bottom-right'),
(8,		'008.png',	0,	1,	'top-left'),
(9,		'009.png',	0,	1,	'bottom-right'),
(10,	'010.png',	0,	1,	'bottom-left'),
(11,	'011.png',	0,	1,	'bottom-center'),
(12,	'012.png',	0,	1,	'bottom-left'),
(13,	'013.png',	0,	1,	'bottom-left'),
(14,	'014.png',	0,	1,	'bottom-left'),
(15,	'015.png',	0,	1,	'bottom-left'),
(16,	'016.png',	0,	1,	'bottom-right'),
(17,	'017.png',	0,	1,	'bottom-right'),
(18,	'018.png',	0,	1,	'bottom-left'),
(19,	'019.png',	0,	1,	'bottom-right'),
(20,	'020.png',	0,	1,	'bottom-center'),
(21,	'021.png',	0,	0,	'top-right'),
(22,	'022.png',	0,	1,	'bottom-right'),
(23,	'023.png',	0,	1,	'bottom-left'),
(24,	'024.png',	0,	1,	'bottom-center'),
(25,	'025.png',	0,	1,	'bottom-center'),
(26,	'026.png',	0,	1,	'bottom-right'),
(27,	'027.png',	0,	1,	'bottom-right'),
(28,	'028.png',	0,	1,	'center-center'),
(29,	'029.png',	0,	1,	'bottom-left'),
(30,	'030.png',	0,	1,	'bottom-center'),
(31,	'031.png',	0,	1,	'bottom-left'),
(32,	'032.png',	0,	1,	'bottom-left'),
(33,	'033.png',	0,	1,	'bottom-right'),
(34,	'034.png',	0,	1,	'center-center'),
(35,	'035.png',	0,	1,	'center-center'),
(36,	'036.png',	0,	1,	'bottom-left'),
(37,	'037.png',	0,	1,	'bottom-right'),
(38,	'038.png',	0,	1,	'bottom-left'),
(39,	'039.png',	0,	1,	'bottom-left'),
(40,	'040.png',	0,	1,	'bottom-center'),
(41,	'041.png',	0,	1,	'bottom-left'),
(42,	'042.png',	0,	1,	'center-center'),
(43,	'043.png',	0,	1,	'bottom-right'),
(44,	'044.png',	0,	1,	'bottom-left'),
(45,	'045.png',	0,	1,	'bottom-center'),
(46,	'046.png',	0,	1,	'bottom-center'),
(47,	'047.png',	0,	1,	'center-center'),
(48,	'048.png',	0,	1,	'bottom-left'),
(49,	'049.png',	0,	1,	'bottom-left'),
(50,	'050.png',	0,	1,	'bottom-left'),
(51,	'051.png',	0,	1,	'top-right'),
(52,	'052.png',	0,	1,	'center-left'),
(53,	'053.png',	0,	1,	'bottom-right'),
(54,	'054.png',	0,	1,	'bottom-left'),
(55,	'055.png',	0,	1,	'bottom-left'),
(56,	'056.png',	0,	1,	'bottom-left'),
(57,	'057.png',	0,	1,	'bottom-left'),
(58,	'058.png',	0,	1,	'top-right'),
(59,	'059.png',	0,	1,	'bottom-center'),
(60,	'060.png',	0,	1,	'bottom-center'),
(61,	'061.png',	0,	1,	'bottom-center'),
(62,	'062.png',	0,	1,	'bottom-center'),
(63,	'063.png',	0,	1,	'bottom-center'),
(64,	'064.png',	0,	1,	'bottom-right'),
(65,	'065.png',	0,	1,	'center-center'),
(66,	'066.png',	0,	1,	'bottom-center'),
(67,	'067.png',	0,	1,	'bottom-center'),
(68,	'068.png',	0,	1,	'bottom-right'),
(69,	'069.png',	0,	1,	'bottom-center'),
(70,	'070.png',	0,	1,	'bottom-center'),
(71,	'071.png',	0,	1,	'bottom-left'),
(72,	'072.png',	0,	1,	'bottom-center'),
(73,	'073.png',	0,	1,	'center-center'),
(74,	'074.png',	0,	1,	'center-center'),
(75,	'075.png',	0,	1,	'bottom-right'),
(76,	'076.png',	0,	1,	'bottom-left'),
(77,	'077.png',	0,	1,	'bottom-left'),
(78,	'078.png',	0,	1,	'bottom-right'),
(79,	'079.png',	0,	1,	'bottom-left'),
(80,	'080.png',	0,	1,	'bottom-right'),
(81,	'081.png',	0,	1,	'bottom-center'),
(82,	'082.png',	0,	1,	'bottom-left'),
(83,	'083.png',	0,	1,	'bottom-right'),
(84,	'084.png',	0,	1,	'bottom-right'),
(85,	'085.png',	0,	1,	'bottom-left'),
(86,	'086.png',	0,	1,	'bottom-left'),
(87,	'087.png',	0,	1,	'bottom-right'),
(88,	'088.png',	0,	1,	'bottom-right'),
(89,	'089.png',	0,	1,	'top-left'),
(90,	'090.png',	0,	1,	'bottom-left'),
(91,	'091.png',	0,	1,	'top-right'),
(92,	'092.png',	0,	1,	'bottom-left'),
(93,	'093.png',	0,	1,	'bottom-right'),
(94,	'094.png',	0,	1,	'center-center'),
(95,	'095.png',	0,	1,	'center-right'),
(96,	'096.png',	0,	1,	'center-center'),
(97,	'097.png',	0,	1,	'center-center'),
(98,	'098.png',	0,	1,	'center-center'),
(99,	'099.png',	0,	1,	'center-center'),
(100,	'100.png',	0,	1,	'center-center'),
(101,	'101.png',	0,	1,	'center-right'),
(102,	'102.png',	0,	1,	'bottom-center'),
(103,	'103.png',	0,	1,	'center-right'),
(104,	'104.png',	0,	1,	'bottom-center'),
(105,	'105.png',	0,	1,	'center-center'),
(106,	'106.png',	0,	1,	'center-center'),
(107,	'107.png',	0,	1,	'bottom-right'),
(108,	'108.png',	0,	1,	'center-center'),
(109,	'109.png',	0,	1,	'center-center'),
(110,	'110.png',	0,	1,	'center-center'),
(111,	'111.png',	0,	1,	'bottom-right'),
(112,	'112.png',	0,	1,	'bottom-left'),
(113,	'113.png',	0,	1,	'center-right'),
(114,	'114.png',	0,	1,	'bottom-center'),
(115,	'115.png',	0,	1,	'bottom-center'),
(116,	'116.png',	0,	1,	'center-left'),
(117,	'117.png',	0,	1,	'center-center'),
(118,	'118.png',	0,	1,	'center-center'),
(119,	'119.png',	0,	1,	'bottom-center'),
(120,	'120.png',	0,	1,	'bottom-center'),
(121,	'121.png',	0,	1,	'top-right'),
(122,	'122.png',	0,	1,	'center-center'),
(123,	'123.png',	0,	1,	'bottom-right'),
(124,	'124.png',	0,	1,	'top-left'),
(125,	'125.png',	0,	1,	'top-left'),
(126,	'126.png',	0,	1,	'bottom-left'),
(127,	'127.png',	0,	1,	'bottom-center'),
(128,	'128.png',	0,	1,	'bottom-right'),
(129,	'129.png',	0,	1,	'bottom-center'),
(130,	'130.png',	0,	1,	'bottom-center'),
(131,	'131.png',	0,	1,	'bottom-right'),
(132,	'132.png',	0,	1,	'bottom-right'),
(133,	'133.png',	0,	1,	'bottom-center'),
(134,	'001.webm',	1,	1,	'bottom-center'),
(135,	'002.webm',	1,	0,	'top-left'),
(136,	'003.webm',	1,	1,	'bottom-right'),
(137,	'004.webm',	1,	1,	'center-center'),
(138,	'005.webm',	1,	1,	'bottom-center'),
(139,	'006.webm',	1,	0,	'top-left'),
(140,	'007.webm',	1,	1,	'top-center'),
(141,	'008.webm',	1,	0,	'top-left'),
(142,	'009.webm',	1,	1,	'bottom-left'),
(143,	'010.webm',	1,	1,	'center-center'),
(144,	'011.webm',	1,	1,	'bottom-right'),
(145,	'012.webm',	1,	1,	'bottom-right'),
(146,	'013.webm',	1,	1,	'bottom-center'),
(147,	'014.webm',	1,	1,	'bottom-center'),
(148,	'015.webm',	1,	1,	'bottom-right'),
(149,	'016.webm',	1,	1,	'bottom-center');

-- 2021-04-12 15:09:03
