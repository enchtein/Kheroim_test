-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 18 2020 г., 23:51
-- Версия сервера: 10.3.13-MariaDB
-- Версия PHP: 7.1.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `nexteum`
--

-- --------------------------------------------------------

--
-- Структура таблицы `lanching`
--

CREATE TABLE `lanching` (
  `id` int(11) NOT NULL,
  `user_ip` varchar(256) NOT NULL,
  `user_browser` varchar(256) NOT NULL,
  `time_of_csv` datetime NOT NULL,
  `path_to_csv` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `parser`
--

CREATE TABLE `parser` (
  `id` int(11) NOT NULL,
  `ORIGINAL_link` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `preview` varchar(256) NOT NULL,
  `descr` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `lanching`
--
ALTER TABLE `lanching`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `parser`
--
ALTER TABLE `parser`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `lanching`
--
ALTER TABLE `lanching`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `parser`
--
ALTER TABLE `parser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
