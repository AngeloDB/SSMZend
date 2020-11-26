-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Nov 26, 2020 alle 10:52
-- Versione del server: 8.0.22-0ubuntu0.20.04.2
-- Versione PHP: 7.1.33-24+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crewplan_production`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `activity`
--

CREATE TABLE `activity` (
  `idActivity` int NOT NULL,
  `Action` varchar(50) NOT NULL,
  `Start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastPing` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_utente` int NOT NULL,
  `UserCanModify` tinyint(1) NOT NULL,
  `Modified` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `aiuto`
--

CREATE TABLE `aiuto` (
  `idAiuto` int NOT NULL,
  `Controller` varchar(128) NOT NULL,
  `Action` varchar(128) NOT NULL,
  `NoteTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Help1` text NOT NULL,
  `Help2` text NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `allegati`
--

CREATE TABLE `allegati` (
  `idAllegato` int NOT NULL,
  `Creato` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Nome` varchar(128) DEFAULT NULL,
  `StoreName` varchar(128) DEFAULT NULL,
  `Tipo` varchar(128) DEFAULT NULL,
  `Dimensione` int DEFAULT NULL,
  `Scadenza` date DEFAULT NULL,
  `Registrazione` date DEFAULT NULL,
  `Content` mediumblob,
  `Note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `alleguests`
--

CREATE TABLE `alleguests` (
  `idAlleGuest` int NOT NULL,
  `Creato` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idAllegato` int DEFAULT NULL,
  `idTab_guest` int DEFAULT NULL,
  `Guest` varchar(25) DEFAULT NULL,
  `idGuestkey` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `aree`
--

CREATE TABLE `aree` (
  `idArea` int NOT NULL,
  `idProgetto` int NOT NULL,
  `idContratto` int NOT NULL,
  `Ordine` int NOT NULL,
  `Area` varchar(128) NOT NULL,
  `AreaShort` varchar(80) NOT NULL,
  `Vendor` int NOT NULL,
  `NotInvoiced` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `autorizz_progetti`
--

CREATE TABLE `autorizz_progetti` (
  `idAutorizzProgetto` int NOT NULL,
  `id_utente` int NOT NULL,
  `idProgetto` int NOT NULL,
  `Attivo` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `business_units`
--

CREATE TABLE `business_units` (
  `idBusinessUnit` int NOT NULL,
  `idCliente` int NOT NULL,
  `Descrizione` varchar(128) NOT NULL,
  `DescShort` varchar(25) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `calendars`
--

CREATE TABLE `calendars` (
  `idCalendar` int NOT NULL,
  `Nome` varchar(128) NOT NULL,
  `Descrizione` varchar(256) NOT NULL,
  `Lun` int NOT NULL,
  `Mar` int NOT NULL,
  `Mer` int NOT NULL,
  `Gio` int NOT NULL,
  `Ven` int NOT NULL,
  `Sab` int NOT NULL,
  `Dom` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `clienti`
--

CREATE TABLE `clienti` (
  `idCliente` int NOT NULL,
  `RagSoc` varchar(255) NOT NULL,
  `Nome` varchar(128) NOT NULL,
  `Citta` varchar(128) NOT NULL,
  `Indirizzo` varchar(255) NOT NULL,
  `Telefono` varchar(36) NOT NULL,
  `Responsabile` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `contratti`
--

CREATE TABLE `contratti` (
  `idContratto` int NOT NULL,
  `Contratto` varchar(256) NOT NULL,
  `Sigla` varchar(50) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `contratti_forn_pers`
--

CREATE TABLE `contratti_forn_pers` (
  `idContrattoFornPers` int NOT NULL,
  `idFornPers` int NOT NULL,
  `idQualifica` int NOT NULL,
  `idProgetto` int NOT NULL,
  `idProgStep` decimal(10,2) NOT NULL,
  `tariffaOraria` decimal(10,2) NOT NULL,
  `tariffaProgetto` decimal(10,2) NOT NULL,
  `tariffaStep` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `dett_giorni_lav`
--

CREATE TABLE `dett_giorni_lav` (
  `idDettGGLav` int NOT NULL,
  `idGiorniLav` int NOT NULL,
  `idRichiesta` int NOT NULL,
  `Anno` int NOT NULL,
  `Mese` int NOT NULL,
  `TotGGLav` int NOT NULL,
  `TotGGNR` int DEFAULT NULL,
  `OreTot` int NOT NULL,
  `Status` char(1) NOT NULL,
  `OreStr` int NOT NULL,
  `OreGG_01` int DEFAULT NULL,
  `TipoGG_01` char(1) DEFAULT NULL,
  `FattGG_01` char(1) DEFAULT NULL,
  `NoteGG_01` text,
  `OreGG_02` int DEFAULT NULL,
  `TipoGG_02` char(1) DEFAULT NULL,
  `FattGG_02` char(1) DEFAULT NULL,
  `NoteGG_02` text,
  `OreGG_03` int DEFAULT NULL,
  `TipoGG_03` char(1) DEFAULT NULL,
  `FattGG_03` char(1) DEFAULT NULL,
  `NoteGG_03` text,
  `OreGG_04` int DEFAULT NULL,
  `TipoGG_04` char(1) DEFAULT NULL,
  `FattGG_04` char(1) DEFAULT NULL,
  `NoteGG_04` text,
  `OreGG_05` int DEFAULT NULL,
  `TipoGG_05` char(1) DEFAULT NULL,
  `FattGG_05` char(1) DEFAULT NULL,
  `NoteGG_05` text,
  `OreGG_06` int DEFAULT NULL,
  `TipoGG_06` char(1) DEFAULT NULL,
  `FattGG_06` char(1) DEFAULT NULL,
  `NoteGG_06` text,
  `OreGG_07` int DEFAULT NULL,
  `TipoGG_07` char(1) DEFAULT NULL,
  `FattGG_07` char(1) DEFAULT NULL,
  `NoteGG_07` text,
  `OreGG_08` int DEFAULT NULL,
  `TipoGG_08` char(1) DEFAULT NULL,
  `FattGG_08` char(1) DEFAULT NULL,
  `NoteGG_08` text,
  `OreGG_09` int DEFAULT NULL,
  `TipoGG_09` char(1) DEFAULT NULL,
  `FattGG_09` char(1) DEFAULT NULL,
  `NoteGG_09` text,
  `OreGG_10` int DEFAULT NULL,
  `TipoGG_10` char(1) DEFAULT NULL,
  `FattGG_10` char(1) DEFAULT NULL,
  `NoteGG_10` text,
  `OreGG_11` int DEFAULT NULL,
  `TipoGG_11` char(1) DEFAULT NULL,
  `FattGG_11` char(1) DEFAULT NULL,
  `NoteGG_11` text,
  `OreGG_12` int DEFAULT NULL,
  `TipoGG_12` char(1) DEFAULT NULL,
  `FattGG_12` char(1) DEFAULT NULL,
  `NoteGG_12` text,
  `OreGG_13` int DEFAULT NULL,
  `TipoGG_13` char(1) DEFAULT NULL,
  `FattGG_13` char(1) DEFAULT NULL,
  `NoteGG_13` text,
  `OreGG_14` int DEFAULT NULL,
  `TipoGG_14` char(1) DEFAULT NULL,
  `FattGG_14` char(1) DEFAULT NULL,
  `NoteGG_14` text,
  `OreGG_15` int DEFAULT NULL,
  `TipoGG_15` char(1) DEFAULT NULL,
  `FattGG_15` char(1) DEFAULT NULL,
  `NoteGG_15` text,
  `OreGG_16` int DEFAULT NULL,
  `TipoGG_16` char(1) DEFAULT NULL,
  `FattGG_16` char(1) DEFAULT NULL,
  `NoteGG_16` text,
  `OreGG_17` int DEFAULT NULL,
  `TipoGG_17` char(1) DEFAULT NULL,
  `FattGG_17` char(1) DEFAULT NULL,
  `NoteGG_17` text,
  `OreGG_18` int DEFAULT NULL,
  `TipoGG_18` char(1) DEFAULT NULL,
  `FattGG_18` char(1) DEFAULT NULL,
  `NoteGG_18` text,
  `OreGG_19` int DEFAULT NULL,
  `TipoGG_19` char(1) DEFAULT NULL,
  `FattGG_19` char(1) DEFAULT NULL,
  `NoteGG_19` text,
  `OreGG_20` int DEFAULT NULL,
  `TipoGG_20` char(1) DEFAULT NULL,
  `FattGG_20` char(1) DEFAULT NULL,
  `NoteGG_20` text,
  `OreGG_21` int DEFAULT NULL,
  `TipoGG_21` char(1) DEFAULT NULL,
  `FattGG_21` char(1) DEFAULT NULL,
  `NoteGG_21` text,
  `OreGG_22` int DEFAULT NULL,
  `TipoGG_22` char(1) DEFAULT NULL,
  `FattGG_22` char(1) DEFAULT NULL,
  `NoteGG_22` text,
  `OreGG_23` int DEFAULT NULL,
  `TipoGG_23` char(1) DEFAULT NULL,
  `FattGG_23` char(1) DEFAULT NULL,
  `NoteGG_23` text,
  `OreGG_24` int DEFAULT NULL,
  `TipoGG_24` char(1) DEFAULT NULL,
  `FattGG_24` char(1) DEFAULT NULL,
  `NoteGG_24` text,
  `OreGG_25` int DEFAULT NULL,
  `TipoGG_25` char(1) DEFAULT NULL,
  `FattGG_25` char(1) DEFAULT NULL,
  `NoteGG_25` text,
  `OreGG_26` int DEFAULT NULL,
  `TipoGG_26` char(1) DEFAULT NULL,
  `FattGG_26` char(1) DEFAULT NULL,
  `NoteGG_26` text,
  `OreGG_27` int DEFAULT NULL,
  `TipoGG_27` char(1) DEFAULT NULL,
  `FattGG_27` char(1) DEFAULT NULL,
  `NoteGG_27` text,
  `OreGG_28` int DEFAULT NULL,
  `TipoGG_28` char(1) DEFAULT NULL,
  `FattGG_28` char(1) DEFAULT NULL,
  `NoteGG_28` text,
  `OreGG_29` int DEFAULT NULL,
  `TipoGG_29` char(1) DEFAULT NULL,
  `FattGG_29` char(1) DEFAULT NULL,
  `NoteGG_29` text,
  `OreGG_30` int DEFAULT NULL,
  `TipoGG_30` char(1) DEFAULT NULL,
  `FattGG_30` char(1) DEFAULT NULL,
  `NoteGG_30` text,
  `OreGG_31` int DEFAULT NULL,
  `TipoGG_31` char(1) DEFAULT NULL,
  `FattGG_31` char(1) DEFAULT NULL,
  `NoteGG_31` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `eventi`
--

CREATE TABLE `eventi` (
  `idEvento` int NOT NULL,
  `idProgetto` int NOT NULL,
  `Evento` varchar(128) NOT NULL,
  `Data` date NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `forn_pers`
--

CREATE TABLE `forn_pers` (
  `idFornPers` int NOT NULL,
  `RagSoc` varchar(255) NOT NULL,
  `Nome` varchar(128) NOT NULL,
  `Tipo` varchar(1) NOT NULL,
  `Citta` varchar(128) NOT NULL,
  `Indirizzo` varchar(255) NOT NULL,
  `Riferimento` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Telefono` varchar(50) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `forn_pers_old`
--

CREATE TABLE `forn_pers_old` (
  `idFornPers` int NOT NULL,
  `RagSoc` varchar(255) NOT NULL,
  `Nome` varchar(128) NOT NULL,
  `Citta` varchar(128) NOT NULL,
  `Indirizzo` varchar(255) NOT NULL,
  `Riferimento` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Telefono` varchar(50) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `foto-par`
--

CREATE TABLE `foto-par` (
  `idFotoPar` int NOT NULL,
  `idAllegato` int NOT NULL,
  `startX` float NOT NULL,
  `startY` float NOT NULL,
  `Altezza` float NOT NULL,
  `Larghezza` float NOT NULL,
  `Rotazione` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `Gantt`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `Gantt` (
`Actual` bigint
,`Anno` int
,`AreaShort` varchar(80)
,`Cognome` varchar(255)
,`Commessa` varchar(255)
,`FattGG_01` char(1)
,`FattGG_02` char(1)
,`FattGG_03` char(1)
,`FattGG_04` char(1)
,`FattGG_05` char(1)
,`FattGG_06` char(1)
,`FattGG_07` char(1)
,`FattGG_08` char(1)
,`FattGG_09` char(1)
,`FattGG_10` char(1)
,`FattGG_11` char(1)
,`FattGG_12` char(1)
,`FattGG_13` char(1)
,`FattGG_14` char(1)
,`FattGG_15` char(1)
,`FattGG_16` char(1)
,`FattGG_17` char(1)
,`FattGG_18` char(1)
,`FattGG_19` char(1)
,`FattGG_20` char(1)
,`FattGG_21` char(1)
,`FattGG_22` char(1)
,`FattGG_23` char(1)
,`FattGG_24` char(1)
,`FattGG_25` char(1)
,`FattGG_26` char(1)
,`FattGG_27` char(1)
,`FattGG_28` char(1)
,`FattGG_29` char(1)
,`FattGG_30` char(1)
,`FattGG_31` char(1)
,`Fine` date
,`GiorniEff` int
,`GiorniTarget` int
,`hasDetail` int
,`idArea` int
,`idDettGGLav` int
,`idGiorniLav` int
,`idPersonale` int
,`idProgetto` int
,`idProgStep` int
,`idQualifica` int
,`Inizio` date
,`Mese` int
,`Nome` varchar(255)
,`OreGG_01` int
,`OreGG_02` int
,`OreGG_03` int
,`OreGG_04` int
,`OreGG_05` int
,`OreGG_06` int
,`OreGG_07` int
,`OreGG_08` int
,`OreGG_09` int
,`OreGG_10` int
,`OreGG_11` int
,`OreGG_12` int
,`OreGG_13` int
,`OreGG_14` int
,`OreGG_15` int
,`OreGG_16` int
,`OreGG_17` int
,`OreGG_18` int
,`OreGG_19` int
,`OreGG_20` int
,`OreGG_21` int
,`OreGG_22` int
,`OreGG_23` int
,`OreGG_24` int
,`OreGG_25` int
,`OreGG_26` int
,`OreGG_27` int
,`OreGG_28` int
,`OreGG_29` int
,`OreGG_30` int
,`OreGG_31` int
,`Planned` bigint
,`Qualifica` varchar(255)
,`Step` varchar(255)
,`TipoGG_01` char(1)
,`TipoGG_02` char(1)
,`TipoGG_03` char(1)
,`TipoGG_04` char(1)
,`TipoGG_05` char(1)
,`TipoGG_06` char(1)
,`TipoGG_07` char(1)
,`TipoGG_08` char(1)
,`TipoGG_09` char(1)
,`TipoGG_10` char(1)
,`TipoGG_11` char(1)
,`TipoGG_12` char(1)
,`TipoGG_13` char(1)
,`TipoGG_14` char(1)
,`TipoGG_15` char(1)
,`TipoGG_16` char(1)
,`TipoGG_17` char(1)
,`TipoGG_18` char(1)
,`TipoGG_19` char(1)
,`TipoGG_20` char(1)
,`TipoGG_21` char(1)
,`TipoGG_22` char(1)
,`TipoGG_23` char(1)
,`TipoGG_24` char(1)
,`TipoGG_25` char(1)
,`TipoGG_26` char(1)
,`TipoGG_27` char(1)
,`TipoGG_28` char(1)
,`TipoGG_29` char(1)
,`TipoGG_30` char(1)
,`TipoGG_31` char(1)
);

-- --------------------------------------------------------

--
-- Struttura della tabella `giorni_lav`
--

CREATE TABLE `giorni_lav` (
  `idGiorniLav` int NOT NULL,
  `idRichiesta` int NOT NULL,
  `Anno` int NOT NULL,
  `Mese` int NOT NULL,
  `GiorniTarget` int DEFAULT NULL,
  `GiorniEff` int DEFAULT NULL,
  `FerieEff` int NOT NULL,
  `PermEff` int NOT NULL,
  `MalEff` int NOT NULL,
  `Note` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `guesthost`
--

CREATE TABLE `guesthost` (
  `idGuestHost` int NOT NULL,
  `Creato` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idTab_guest` int DEFAULT NULL,
  `idGuest` int DEFAULT NULL,
  `idTab_host` int NOT NULL,
  `idHost` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `jobs`
--

CREATE TABLE `jobs` (
  `idJob` int NOT NULL,
  `idPersonale` int NOT NULL,
  `idRichiesta` int NOT NULL,
  `Inizio` date NOT NULL,
  `Fine` date NOT NULL,
  `SalGG` decimal(10,2) NOT NULL,
  `SalOrario` decimal(10,2) NOT NULL,
  `PocketMoney` decimal(10,2) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `locations`
--

CREATE TABLE `locations` (
  `idLocation` int NOT NULL,
  `idProgetto` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Paese` varchar(255) NOT NULL,
  `Indirizzo` varchar(255) NOT NULL,
  `Latitudine` float NOT NULL,
  `Longitudine` float NOT NULL,
  `idVisaType` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `locations_bak`
--

CREATE TABLE `locations_bak` (
  `idLocation` int NOT NULL,
  `idProgetto` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Paese` varchar(255) NOT NULL,
  `Indirizzo` varchar(255) NOT NULL,
  `Latitudine` float NOT NULL,
  `Longitudine` float NOT NULL,
  `idVisaType` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `login`
--

CREATE TABLE `login` (
  `id` int NOT NULL,
  `username` varchar(32) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_utente` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `mappe`
--

CREATE TABLE `mappe` (
  `idMappa` int NOT NULL,
  `Sigla` varchar(2) NOT NULL,
  `Mappa` varchar(80) NOT NULL,
  `File` varchar(255) NOT NULL,
  `Nome` varchar(80) NOT NULL,
  `MapHeight` float NOT NULL,
  `MapWidth` float NOT NULL,
  `Scala` varchar(5) NOT NULL,
  `DotSize` int NOT NULL,
  `LatN` float NOT NULL,
  `LatS` float NOT NULL,
  `LongW` float NOT NULL,
  `LongE` float NOT NULL,
  `Note` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `new_pers`
--

CREATE TABLE `new_pers` (
  `idNewPers` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Cognome` varchar(255) NOT NULL,
  `CodFisc` varchar(25) NOT NULL,
  `DataNascita` date NOT NULL,
  `LuogoNascita` varchar(255) NOT NULL,
  `PaeseResidenza` varchar(255) NOT NULL,
  `IndResidenza` varchar(255) NOT NULL,
  `PaeseDomicilio` varchar(255) NOT NULL,
  `IndDomicilio` varchar(255) NOT NULL,
  `Email` varchar(128) NOT NULL,
  `Email2` varchar(128) NOT NULL,
  `Telefono` varchar(25) NOT NULL,
  `TelMobile` varchar(25) NOT NULL,
  `TelMobileLocal` varchar(25) NOT NULL,
  `Padre` varchar(128) NOT NULL,
  `Madre` varchar(128) NOT NULL,
  `idQualifica` int DEFAULT NULL,
  `idFornPers` int DEFAULT NULL,
  `IdTabStatoNewPers` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `note`
--

CREATE TABLE `note` (
  `idNote` int NOT NULL,
  `idRefer` int NOT NULL,
  `Object` varchar(255) NOT NULL,
  `DateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idStartTread` int NOT NULL,
  `Message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `passaporti`
--

CREATE TABLE `passaporti` (
  `idPass` int NOT NULL,
  `idPersonale` int NOT NULL,
  `Numero` varchar(36) NOT NULL,
  `DataRilascio` date NOT NULL,
  `LuogoRilascio` varchar(255) NOT NULL,
  `RilasciatoDa` varchar(255) NOT NULL,
  `Scadenza` date NOT NULL,
  `Limitazioni` text NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `personale`
--

CREATE TABLE `personale` (
  `idPersonale` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Cognome` varchar(255) NOT NULL,
  `CodFisc` varchar(25) NOT NULL,
  `DataNascita` date NOT NULL,
  `LuogoNascita` varchar(255) NOT NULL,
  `PaeseResidenza` varchar(255) NOT NULL,
  `IndResidenza` varchar(255) NOT NULL,
  `PaeseDomicilio` varchar(255) NOT NULL,
  `IndDomicilio` varchar(255) NOT NULL,
  `Email` varchar(128) NOT NULL,
  `Email2` varchar(128) NOT NULL,
  `Telefono` varchar(25) NOT NULL,
  `TelMobile` varchar(25) NOT NULL,
  `TelMobileLocal` varchar(25) NOT NULL,
  `Padre` varchar(128) NOT NULL,
  `Madre` varchar(128) NOT NULL,
  `idQualifica` int DEFAULT NULL,
  `idFornPers` int DEFAULT NULL,
  `Resident` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `pers_forn_pers`
--

CREATE TABLE `pers_forn_pers` (
  `idPersFornPers` int NOT NULL,
  `idPersonale` int NOT NULL,
  `idFornPers` int NOT NULL,
  `Resident` int NOT NULL,
  `idQualifica` int NOT NULL,
  `idProgetto` int DEFAULT NULL,
  `Dal` date NOT NULL,
  `Al` date NOT NULL,
  `RateHH` decimal(10,2) DEFAULT NULL,
  `RateGG` decimal(10,2) DEFAULT NULL,
  `PocketMoney` decimal(10,2) DEFAULT NULL,
  `PurchaseOrder` varchar(128) DEFAULT NULL,
  `Ordine` int DEFAULT NULL,
  `Note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `pers_qual_poss`
--

CREATE TABLE `pers_qual_poss` (
  `idPersQualPoss` int NOT NULL,
  `idPersonale` int NOT NULL,
  `idFornPers` int NOT NULL,
  `salGG` decimal(10,2) NOT NULL,
  `salOra` decimal(10,2) NOT NULL,
  `Valutazione` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `progetti`
--

CREATE TABLE `progetti` (
  `idProgetto` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `JobNumber` varchar(50) NOT NULL,
  `JobNumber2` varchar(50) NOT NULL,
  `HQ_Coordinator` int DEFAULT NULL,
  `ProjectManager` int NOT NULL,
  `PWMainContractor` int NOT NULL,
  `Revisione` int NOT NULL,
  `DataRevisione` date NOT NULL,
  `idUtenteRevisione` int NOT NULL,
  `OnHold` int NOT NULL,
  `Inizio` date NOT NULL,
  `Fine` date NOT NULL,
  `Impianto` varchar(255) NOT NULL,
  `Paese` varchar(255) NOT NULL,
  `Indirizzo` varchar(255) NOT NULL,
  `Latitudine` float NOT NULL,
  `Longitudine` float NOT NULL,
  `idVisaType` int NOT NULL,
  `idCliente` int NOT NULL,
  `idClienteFin` int NOT NULL,
  `idBusinessUnit` int NOT NULL,
  `idContratto` int NOT NULL,
  `idTabValuta` int NOT NULL,
  `idCalendar` int DEFAULT NULL,
  `Rotation` varchar(25) NOT NULL,
  `IdTabOreGG` int DEFAULT NULL,
  `idTabContrMd` int NOT NULL,
  `PocketMoney` decimal(12,2) NOT NULL,
  `ODFcosts` decimal(12,2) DEFAULT NULL,
  `Reference` date NOT NULL,
  `CashReference` date NOT NULL,
  `Budget` decimal(12,2) NOT NULL,
  `MSCBudget` decimal(12,2) NOT NULL,
  `ContractNum` varchar(50) NOT NULL,
  `ContractDate` date NOT NULL,
  `ContractValue` decimal(12,2) NOT NULL,
  `LetterOfCredit` int NOT NULL,
  `LCExpiration` date NOT NULL,
  `ContractMD` int NOT NULL,
  `MDPrice` decimal(8,2) NOT NULL,
  `Invoicing` text NOT NULL,
  `Note` text NOT NULL,
  `ContractNote` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prog_steps`
--

CREATE TABLE `prog_steps` (
  `idProgStep` int NOT NULL,
  `idProgetto` int NOT NULL,
  `idArea` int NOT NULL,
  `idContratto` int NOT NULL,
  `Ordine` int NOT NULL,
  `Step` varchar(255) NOT NULL,
  `Inizio` date NOT NULL,
  `Fine` date NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prog_steps_req_template`
--

CREATE TABLE `prog_steps_req_template` (
  `idProgStepReqTemplate` int NOT NULL,
  `idProgStepTemplate` int NOT NULL,
  `idQualifica` int NOT NULL,
  `NumReq` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prog_steps_template`
--

CREATE TABLE `prog_steps_template` (
  `idProgStepTemplate` int NOT NULL,
  `idProgTemplate` int NOT NULL,
  `Step` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prog_template`
--

CREATE TABLE `prog_template` (
  `idProgTemplate` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `pw_contratti`
--

CREATE TABLE `pw_contratti` (
  `idPwContratto` int NOT NULL,
  `idProgetto` int NOT NULL,
  `idContratto` int NOT NULL,
  `idTabValuta` int NOT NULL,
  `idTabContrMd` int NOT NULL,
  `Budget` decimal(12,2) NOT NULL,
  `ContractNum` varchar(50) NOT NULL,
  `ContractDate` date NOT NULL,
  `ContractValue` decimal(12,2) NOT NULL,
  `LetterOfCredit` int NOT NULL,
  `LCExpiration` date NOT NULL,
  `ContractMD` int NOT NULL,
  `MDPrice` decimal(8,2) NOT NULL,
  `Invoicing` text NOT NULL,
  `Note` text NOT NULL,
  `ContractNote` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `qualifiche`
--

CREATE TABLE `qualifiche` (
  `idQualifica` int NOT NULL,
  `idRagg` int NOT NULL,
  `NumRagg` int NOT NULL,
  `Raggruppamento` varchar(255) NOT NULL,
  `SiglaDesc` varchar(4) NOT NULL,
  `Descrizione` varchar(255) NOT NULL,
  `tarOrariaBase` decimal(10,2) NOT NULL,
  `tarGgBase` decimal(10,2) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `raggruppamenti`
--

CREATE TABLE `raggruppamenti` (
  `idRagg` int NOT NULL,
  `NumRagg` int NOT NULL,
  `Raggruppamento` varchar(80) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `richieste`
--

CREATE TABLE `richieste` (
  `idRichiesta` int NOT NULL,
  `idQualifica` int NOT NULL,
  `idProgStep` int NOT NULL,
  `idTabSchemiLav` int NOT NULL,
  `Ordine` int DEFAULT NULL,
  `salOrario` decimal(10,2) NOT NULL,
  `salGG` decimal(10,2) NOT NULL,
  `GGPlanned` int NOT NULL,
  `idPersonale` int NOT NULL,
  `idContratto` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `savetot`
--

CREATE TABLE `savetot` (
  `idSaveTot` int NOT NULL,
  `id_utente` int NOT NULL,
  `idProgetto` int NOT NULL,
  `Reference` datetime NOT NULL,
  `Versione` int DEFAULT NULL,
  `RefVersione` datetime DEFAULT NULL,
  `RefCash` datetime DEFAULT NULL,
  `TotGG` int NOT NULL,
  `TotContrGG` int NOT NULL,
  `TotPers` int DEFAULT NULL,
  `TotContrPers` int DEFAULT NULL,
  `TotRate` decimal(10,2) NOT NULL,
  `TotContrRate` decimal(10,2) NOT NULL,
  `TotPM` decimal(10,2) NOT NULL,
  `TotContrPM` decimal(10,2) NOT NULL,
  `TotCash` decimal(12,2) DEFAULT NULL,
  `TotContrCash` decimal(12,2) DEFAULT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `savetot_dett`
--

CREATE TABLE `savetot_dett` (
  `idDettSaveTot` int NOT NULL,
  `idSaveTot` int NOT NULL,
  `Anno` int NOT NULL,
  `Mese` int NOT NULL,
  `Pers` int DEFAULT NULL,
  `PersContratto` int DEFAULT NULL,
  `Totale` int NOT NULL,
  `Progressivo` int NOT NULL,
  `TotaleContratto` int NOT NULL,
  `ProgrContratto` int NOT NULL,
  `Cash` decimal(12,2) DEFAULT NULL,
  `CashContratto` decimal(12,2) DEFAULT NULL,
  `Rate` decimal(10,2) NOT NULL,
  `ProgRate` decimal(10,2) NOT NULL,
  `RateContratto` decimal(10,2) NOT NULL,
  `ProgRateContratto` decimal(10,2) NOT NULL,
  `PM` decimal(8,2) NOT NULL,
  `ProgPM` decimal(8,2) NOT NULL,
  `PMContratto` decimal(8,2) NOT NULL,
  `ProgPMContratto` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `spesemese`
--

CREATE TABLE `spesemese` (
  `idSpeseMese` int NOT NULL,
  `idProgetto` int NOT NULL,
  `Anno` int NOT NULL,
  `Mese` int NOT NULL,
  `idRichiesta` int NOT NULL,
  `idTipoSpesa` int NOT NULL,
  `DescTipoSpesa` varchar(80) DEFAULT NULL,
  `Importo` decimal(10,2) NOT NULL,
  `Storno` decimal(10,2) NOT NULL,
  `Riferimento` date NOT NULL,
  `Fattura` varchar(80) NOT NULL,
  `Ordine` varchar(80) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `supervisori`
--

CREATE TABLE `supervisori` (
  `idSupervisore` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Cognome` varchar(255) NOT NULL,
  `idQualifica` int NOT NULL,
  `idFornPers` int NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_contr_md`
--

CREATE TABLE `tab_contr_md` (
  `IdTabContrMd` int NOT NULL,
  `Opzione` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_fatt_gg`
--

CREATE TABLE `tab_fatt_gg` (
  `IdTabFattGG` int NOT NULL,
  `SiglaOpzione` char(3) NOT NULL,
  `Opzione` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_fornpers_types`
--

CREATE TABLE `tab_fornpers_types` (
  `idFornpersType` int NOT NULL,
  `FornpersType` varchar(1) NOT NULL,
  `Descrizione` varchar(128) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_guests`
--

CREATE TABLE `tab_guests` (
  `idTab_guest` int NOT NULL,
  `TableName` varchar(50) DEFAULT NULL,
  `Descrizione` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_hosts`
--

CREATE TABLE `tab_hosts` (
  `idTab_host` int NOT NULL,
  `TableName` varchar(50) DEFAULT NULL,
  `Descrizione` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_ore_gg`
--

CREATE TABLE `tab_ore_gg` (
  `IdTabOreGG` int NOT NULL,
  `Ore` smallint NOT NULL,
  `Opzione` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_schemi_lav`
--

CREATE TABLE `tab_schemi_lav` (
  `idTabSchemiLav` int NOT NULL,
  `descSchemaLav` varchar(50) NOT NULL,
  `oreSett` int NOT NULL,
  `oreMese` int NOT NULL,
  `ggSett` int NOT NULL,
  `ggMese` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_stato_newpers`
--

CREATE TABLE `tab_stato_newpers` (
  `IdTabStatoNewPers` int NOT NULL,
  `SiglaStato` char(3) NOT NULL,
  `Stato` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_tipospesa`
--

CREATE TABLE `tab_tipospesa` (
  `idTabTipoSpesa` int NOT NULL,
  `Descrizione` varchar(80) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_tipo_gg`
--

CREATE TABLE `tab_tipo_gg` (
  `IdTabTipoGG` int NOT NULL,
  `SiglaOpzione` char(3) NOT NULL,
  `Opzione` varchar(255) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_userlevel`
--

CREATE TABLE `tab_userlevel` (
  `id_userlevel` int NOT NULL,
  `desc_userlevel` varchar(50) DEFAULT NULL,
  `desc_ul_short` varchar(25) NOT NULL,
  `num_userlevel` int DEFAULT NULL,
  `single_cant` int DEFAULT NULL,
  `single_impresa` int DEFAULT NULL,
  `Titolo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_valute`
--

CREATE TABLE `tab_valute` (
  `idTabValuta` int NOT NULL,
  `Nome` varchar(128) NOT NULL,
  `CodAlfa` varchar(50) NOT NULL,
  `CodNum` varchar(5) NOT NULL,
  `Simbolo` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tab_visa_types`
--

CREATE TABLE `tab_visa_types` (
  `idVisaType` int NOT NULL,
  `VisaType` varchar(40) NOT NULL,
  `Descrizione` varchar(128) NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id_utente` int NOT NULL,
  `id_cantiere` int DEFAULT NULL,
  `id_impresa` int DEFAULT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `cognome` varchar(50) DEFAULT NULL,
  `interno` varchar(10) DEFAULT NULL,
  `cellulare` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `id_userlevel` int DEFAULT NULL,
  `note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `valute`
--

CREATE TABLE `valute` (
  `idValuta` int NOT NULL,
  `Country_Currency` varchar(128) DEFAULT NULL,
  `Currency_Code` varchar(5) DEFAULT NULL,
  `Graphic_Image` varchar(5) DEFAULT NULL,
  `Font_Code2000` varchar(5) DEFAULT NULL,
  `Font_Arial_Unicode_MS` varchar(5) DEFAULT NULL,
  `Unicode_Decimal` varchar(25) DEFAULT NULL,
  `Unicode_Hex` varchar(25) DEFAULT NULL,
  `Boh` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `visti`
--

CREATE TABLE `visti` (
  `idVisto` int NOT NULL,
  `idPass` int NOT NULL,
  `idVisaType` int NOT NULL,
  `Paese` varchar(128) NOT NULL,
  `Rilascio` date NOT NULL,
  `Scadenza` date NOT NULL,
  `MaxPermanenza` int NOT NULL,
  `Limitazioni` text NOT NULL,
  `Note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura per vista `Gantt`
--
DROP TABLE IF EXISTS `Gantt`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `Gantt`  AS  select `jobs`.`idPersonale` AS `idPersonale`,`giorni_lav`.`idGiorniLav` AS `idGiorniLav`,`giorni_lav`.`GiorniTarget` AS `GiorniTarget`,`giorni_lav`.`GiorniEff` AS `GiorniEff`,`giorni_lav`.`Anno` AS `Anno`,`giorni_lav`.`Mese` AS `Mese`,coalesce(`giorni_lav`.`GiorniTarget`,0) AS `Planned`,coalesce(`giorni_lav`.`GiorniEff`,0) AS `Actual`,`jobs`.`Inizio` AS `Inizio`,`jobs`.`Fine` AS `Fine`,`personale`.`Nome` AS `Nome`,`personale`.`Cognome` AS `Cognome`,`qualifiche`.`idQualifica` AS `idQualifica`,`qualifiche`.`Descrizione` AS `Qualifica`,`prog_steps`.`Step` AS `Step`,`prog_steps`.`idProgStep` AS `idProgStep`,`aree`.`AreaShort` AS `AreaShort`,`aree`.`idArea` AS `idArea`,`dett_giorni_lav`.`idDettGGLav` AS `idDettGGLav`,`dett_giorni_lav`.`OreGG_01` AS `OreGG_01`,`dett_giorni_lav`.`TipoGG_01` AS `TipoGG_01`,`dett_giorni_lav`.`FattGG_01` AS `FattGG_01`,`dett_giorni_lav`.`OreGG_02` AS `OreGG_02`,`dett_giorni_lav`.`TipoGG_02` AS `TipoGG_02`,`dett_giorni_lav`.`FattGG_02` AS `FattGG_02`,`dett_giorni_lav`.`OreGG_03` AS `OreGG_03`,`dett_giorni_lav`.`TipoGG_03` AS `TipoGG_03`,`dett_giorni_lav`.`FattGG_03` AS `FattGG_03`,`dett_giorni_lav`.`OreGG_04` AS `OreGG_04`,`dett_giorni_lav`.`TipoGG_04` AS `TipoGG_04`,`dett_giorni_lav`.`FattGG_04` AS `FattGG_04`,`dett_giorni_lav`.`OreGG_05` AS `OreGG_05`,`dett_giorni_lav`.`TipoGG_05` AS `TipoGG_05`,`dett_giorni_lav`.`FattGG_05` AS `FattGG_05`,`dett_giorni_lav`.`OreGG_06` AS `OreGG_06`,`dett_giorni_lav`.`TipoGG_06` AS `TipoGG_06`,`dett_giorni_lav`.`FattGG_06` AS `FattGG_06`,`dett_giorni_lav`.`OreGG_07` AS `OreGG_07`,`dett_giorni_lav`.`TipoGG_07` AS `TipoGG_07`,`dett_giorni_lav`.`FattGG_07` AS `FattGG_07`,`dett_giorni_lav`.`OreGG_08` AS `OreGG_08`,`dett_giorni_lav`.`TipoGG_08` AS `TipoGG_08`,`dett_giorni_lav`.`FattGG_08` AS `FattGG_08`,`dett_giorni_lav`.`OreGG_09` AS `OreGG_09`,`dett_giorni_lav`.`TipoGG_09` AS `TipoGG_09`,`dett_giorni_lav`.`FattGG_09` AS `FattGG_09`,`dett_giorni_lav`.`OreGG_10` AS `OreGG_10`,`dett_giorni_lav`.`TipoGG_10` AS `TipoGG_10`,`dett_giorni_lav`.`FattGG_10` AS `FattGG_10`,`dett_giorni_lav`.`OreGG_11` AS `OreGG_11`,`dett_giorni_lav`.`TipoGG_11` AS `TipoGG_11`,`dett_giorni_lav`.`FattGG_11` AS `FattGG_11`,`dett_giorni_lav`.`OreGG_12` AS `OreGG_12`,`dett_giorni_lav`.`TipoGG_12` AS `TipoGG_12`,`dett_giorni_lav`.`FattGG_12` AS `FattGG_12`,`dett_giorni_lav`.`OreGG_13` AS `OreGG_13`,`dett_giorni_lav`.`TipoGG_13` AS `TipoGG_13`,`dett_giorni_lav`.`FattGG_13` AS `FattGG_13`,`dett_giorni_lav`.`OreGG_14` AS `OreGG_14`,`dett_giorni_lav`.`TipoGG_14` AS `TipoGG_14`,`dett_giorni_lav`.`FattGG_14` AS `FattGG_14`,`dett_giorni_lav`.`OreGG_15` AS `OreGG_15`,`dett_giorni_lav`.`TipoGG_15` AS `TipoGG_15`,`dett_giorni_lav`.`FattGG_15` AS `FattGG_15`,`dett_giorni_lav`.`OreGG_16` AS `OreGG_16`,`dett_giorni_lav`.`TipoGG_16` AS `TipoGG_16`,`dett_giorni_lav`.`FattGG_16` AS `FattGG_16`,`dett_giorni_lav`.`OreGG_17` AS `OreGG_17`,`dett_giorni_lav`.`TipoGG_17` AS `TipoGG_17`,`dett_giorni_lav`.`FattGG_17` AS `FattGG_17`,`dett_giorni_lav`.`OreGG_18` AS `OreGG_18`,`dett_giorni_lav`.`TipoGG_18` AS `TipoGG_18`,`dett_giorni_lav`.`FattGG_18` AS `FattGG_18`,`dett_giorni_lav`.`OreGG_19` AS `OreGG_19`,`dett_giorni_lav`.`TipoGG_19` AS `TipoGG_19`,`dett_giorni_lav`.`FattGG_19` AS `FattGG_19`,`dett_giorni_lav`.`OreGG_20` AS `OreGG_20`,`dett_giorni_lav`.`TipoGG_20` AS `TipoGG_20`,`dett_giorni_lav`.`FattGG_20` AS `FattGG_20`,`dett_giorni_lav`.`OreGG_21` AS `OreGG_21`,`dett_giorni_lav`.`TipoGG_21` AS `TipoGG_21`,`dett_giorni_lav`.`FattGG_21` AS `FattGG_21`,`dett_giorni_lav`.`OreGG_22` AS `OreGG_22`,`dett_giorni_lav`.`TipoGG_22` AS `TipoGG_22`,`dett_giorni_lav`.`FattGG_22` AS `FattGG_22`,`dett_giorni_lav`.`OreGG_23` AS `OreGG_23`,`dett_giorni_lav`.`TipoGG_23` AS `TipoGG_23`,`dett_giorni_lav`.`FattGG_23` AS `FattGG_23`,`dett_giorni_lav`.`OreGG_24` AS `OreGG_24`,`dett_giorni_lav`.`TipoGG_24` AS `TipoGG_24`,`dett_giorni_lav`.`FattGG_24` AS `FattGG_24`,`dett_giorni_lav`.`OreGG_25` AS `OreGG_25`,`dett_giorni_lav`.`TipoGG_25` AS `TipoGG_25`,`dett_giorni_lav`.`FattGG_25` AS `FattGG_25`,`dett_giorni_lav`.`OreGG_26` AS `OreGG_26`,`dett_giorni_lav`.`TipoGG_26` AS `TipoGG_26`,`dett_giorni_lav`.`FattGG_26` AS `FattGG_26`,`dett_giorni_lav`.`OreGG_27` AS `OreGG_27`,`dett_giorni_lav`.`TipoGG_27` AS `TipoGG_27`,`dett_giorni_lav`.`FattGG_27` AS `FattGG_27`,`dett_giorni_lav`.`OreGG_28` AS `OreGG_28`,`dett_giorni_lav`.`TipoGG_28` AS `TipoGG_28`,`dett_giorni_lav`.`FattGG_28` AS `FattGG_28`,`dett_giorni_lav`.`OreGG_29` AS `OreGG_29`,`dett_giorni_lav`.`TipoGG_29` AS `TipoGG_29`,`dett_giorni_lav`.`FattGG_29` AS `FattGG_29`,`dett_giorni_lav`.`OreGG_30` AS `OreGG_30`,`dett_giorni_lav`.`TipoGG_30` AS `TipoGG_30`,`dett_giorni_lav`.`FattGG_30` AS `FattGG_30`,`dett_giorni_lav`.`OreGG_31` AS `OreGG_31`,`dett_giorni_lav`.`TipoGG_31` AS `TipoGG_31`,`dett_giorni_lav`.`FattGG_31` AS `FattGG_31`,(coalesce(`dett_giorni_lav`.`TipoGG_01`,`dett_giorni_lav`.`FattGG_01`,`dett_giorni_lav`.`TipoGG_02`,`dett_giorni_lav`.`FattGG_02`,`dett_giorni_lav`.`TipoGG_03`,`dett_giorni_lav`.`FattGG_03`,`dett_giorni_lav`.`TipoGG_04`,`dett_giorni_lav`.`FattGG_04`,`dett_giorni_lav`.`TipoGG_05`,`dett_giorni_lav`.`FattGG_05`,`dett_giorni_lav`.`TipoGG_06`,`dett_giorni_lav`.`FattGG_06`,`dett_giorni_lav`.`TipoGG_07`,`dett_giorni_lav`.`FattGG_07`,`dett_giorni_lav`.`TipoGG_08`,`dett_giorni_lav`.`FattGG_08`,`dett_giorni_lav`.`TipoGG_09`,`dett_giorni_lav`.`FattGG_09`,`dett_giorni_lav`.`TipoGG_10`,`dett_giorni_lav`.`FattGG_10`,`dett_giorni_lav`.`TipoGG_11`,`dett_giorni_lav`.`FattGG_11`,`dett_giorni_lav`.`TipoGG_12`,`dett_giorni_lav`.`FattGG_12`,`dett_giorni_lav`.`TipoGG_13`,`dett_giorni_lav`.`FattGG_13`,`dett_giorni_lav`.`TipoGG_14`,`dett_giorni_lav`.`FattGG_14`,`dett_giorni_lav`.`TipoGG_15`,`dett_giorni_lav`.`FattGG_15`,`dett_giorni_lav`.`TipoGG_16`,`dett_giorni_lav`.`FattGG_16`,`dett_giorni_lav`.`TipoGG_17`,`dett_giorni_lav`.`FattGG_17`,`dett_giorni_lav`.`TipoGG_18`,`dett_giorni_lav`.`FattGG_18`,`dett_giorni_lav`.`TipoGG_19`,`dett_giorni_lav`.`FattGG_19`,`dett_giorni_lav`.`TipoGG_20`,`dett_giorni_lav`.`FattGG_20`,`dett_giorni_lav`.`TipoGG_21`,`dett_giorni_lav`.`FattGG_21`,`dett_giorni_lav`.`TipoGG_22`,`dett_giorni_lav`.`FattGG_22`,`dett_giorni_lav`.`TipoGG_23`,`dett_giorni_lav`.`FattGG_23`,`dett_giorni_lav`.`TipoGG_24`,`dett_giorni_lav`.`FattGG_24`,`dett_giorni_lav`.`TipoGG_25`,`dett_giorni_lav`.`FattGG_25`,`dett_giorni_lav`.`TipoGG_26`,`dett_giorni_lav`.`FattGG_26`,`dett_giorni_lav`.`TipoGG_27`,`dett_giorni_lav`.`FattGG_27`,`dett_giorni_lav`.`TipoGG_28`,`dett_giorni_lav`.`FattGG_28`,`dett_giorni_lav`.`TipoGG_29`,`dett_giorni_lav`.`FattGG_29`,`dett_giorni_lav`.`TipoGG_30`,`dett_giorni_lav`.`FattGG_30`,`dett_giorni_lav`.`TipoGG_31`,`dett_giorni_lav`.`FattGG_31`) <> '') AS `hasDetail`,`progetti`.`Nome` AS `Commessa`,`progetti`.`idProgetto` AS `idProgetto` from ((((((((`richieste` join `giorni_lav` on((`giorni_lav`.`idRichiesta` = `richieste`.`idRichiesta`))) left join `dett_giorni_lav` on(((`dett_giorni_lav`.`idRichiesta` = `richieste`.`idRichiesta`) and (`dett_giorni_lav`.`Anno` = `giorni_lav`.`Anno`) and (`dett_giorni_lav`.`Mese` = `giorni_lav`.`Mese`)))) join `jobs` on((`jobs`.`idRichiesta` = `richieste`.`idRichiesta`))) join `personale` on((`personale`.`idPersonale` = `jobs`.`idPersonale`))) join `qualifiche` on((`qualifiche`.`idQualifica` = `richieste`.`idQualifica`))) join `prog_steps` on((`prog_steps`.`idProgStep` = `richieste`.`idProgStep`))) join `aree` on((`aree`.`idArea` = `prog_steps`.`idArea`))) join `progetti` on((`progetti`.`idProgetto` = `prog_steps`.`idProgetto`))) where (((`giorni_lav`.`GiorniTarget` <> 0) or (`giorni_lav`.`GiorniEff` <> 0) or (`dett_giorni_lav`.`TotGGLav` <> 0) or (`dett_giorni_lav`.`TotGGNR` <> 0) or (coalesce(`dett_giorni_lav`.`TipoGG_01`,`dett_giorni_lav`.`FattGG_01`,`dett_giorni_lav`.`TipoGG_02`,`dett_giorni_lav`.`FattGG_02`,`dett_giorni_lav`.`TipoGG_03`,`dett_giorni_lav`.`FattGG_03`,`dett_giorni_lav`.`TipoGG_04`,`dett_giorni_lav`.`FattGG_04`,`dett_giorni_lav`.`TipoGG_05`,`dett_giorni_lav`.`FattGG_05`,`dett_giorni_lav`.`TipoGG_06`,`dett_giorni_lav`.`FattGG_06`,`dett_giorni_lav`.`TipoGG_07`,`dett_giorni_lav`.`FattGG_07`,`dett_giorni_lav`.`TipoGG_08`,`dett_giorni_lav`.`FattGG_08`,`dett_giorni_lav`.`TipoGG_09`,`dett_giorni_lav`.`FattGG_09`,`dett_giorni_lav`.`TipoGG_10`,`dett_giorni_lav`.`FattGG_10`,`dett_giorni_lav`.`TipoGG_11`,`dett_giorni_lav`.`FattGG_11`,`dett_giorni_lav`.`TipoGG_12`,`dett_giorni_lav`.`FattGG_12`,`dett_giorni_lav`.`TipoGG_13`,`dett_giorni_lav`.`FattGG_13`,`dett_giorni_lav`.`TipoGG_14`,`dett_giorni_lav`.`FattGG_14`,`dett_giorni_lav`.`TipoGG_15`,`dett_giorni_lav`.`FattGG_15`,`dett_giorni_lav`.`TipoGG_16`,`dett_giorni_lav`.`FattGG_16`,`dett_giorni_lav`.`TipoGG_17`,`dett_giorni_lav`.`FattGG_17`,`dett_giorni_lav`.`TipoGG_18`,`dett_giorni_lav`.`FattGG_18`,`dett_giorni_lav`.`TipoGG_19`,`dett_giorni_lav`.`FattGG_19`,`dett_giorni_lav`.`TipoGG_20`,`dett_giorni_lav`.`FattGG_20`,`dett_giorni_lav`.`TipoGG_21`,`dett_giorni_lav`.`FattGG_21`,`dett_giorni_lav`.`TipoGG_22`,`dett_giorni_lav`.`FattGG_22`,`dett_giorni_lav`.`TipoGG_23`,`dett_giorni_lav`.`FattGG_23`,`dett_giorni_lav`.`TipoGG_24`,`dett_giorni_lav`.`FattGG_24`,`dett_giorni_lav`.`TipoGG_25`,`dett_giorni_lav`.`FattGG_25`,`dett_giorni_lav`.`TipoGG_26`,`dett_giorni_lav`.`FattGG_26`,`dett_giorni_lav`.`TipoGG_27`,`dett_giorni_lav`.`FattGG_27`,`dett_giorni_lav`.`TipoGG_28`,`dett_giorni_lav`.`FattGG_28`,`dett_giorni_lav`.`TipoGG_29`,`dett_giorni_lav`.`FattGG_29`,`dett_giorni_lav`.`TipoGG_30`,`dett_giorni_lav`.`FattGG_30`,`dett_giorni_lav`.`TipoGG_31`,`dett_giorni_lav`.`FattGG_31`) <> '')) and (`progetti`.`idProgetto` = 1)) order by `progetti`.`Nome`,`prog_steps`.`idProgStep`,`qualifiche`.`idQualifica`,`giorni_lav`.`Anno`,`giorni_lav`.`Mese` ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`idActivity`);

--
-- Indici per le tabelle `aiuto`
--
ALTER TABLE `aiuto`
  ADD PRIMARY KEY (`idAiuto`);

--
-- Indici per le tabelle `allegati`
--
ALTER TABLE `allegati`
  ADD PRIMARY KEY (`idAllegato`);

--
-- Indici per le tabelle `alleguests`
--
ALTER TABLE `alleguests`
  ADD PRIMARY KEY (`idAlleGuest`),
  ADD UNIQUE KEY `idAllegato` (`idAllegato`),
  ADD KEY `Guest` (`Guest`,`idGuestkey`);

--
-- Indici per le tabelle `aree`
--
ALTER TABLE `aree`
  ADD PRIMARY KEY (`idArea`),
  ADD KEY `idProgetto` (`idProgetto`);

--
-- Indici per le tabelle `autorizz_progetti`
--
ALTER TABLE `autorizz_progetti`
  ADD PRIMARY KEY (`idAutorizzProgetto`);

--
-- Indici per le tabelle `business_units`
--
ALTER TABLE `business_units`
  ADD PRIMARY KEY (`idBusinessUnit`);

--
-- Indici per le tabelle `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`idCalendar`);

--
-- Indici per le tabelle `clienti`
--
ALTER TABLE `clienti`
  ADD PRIMARY KEY (`idCliente`);

--
-- Indici per le tabelle `contratti`
--
ALTER TABLE `contratti`
  ADD PRIMARY KEY (`idContratto`);

--
-- Indici per le tabelle `contratti_forn_pers`
--
ALTER TABLE `contratti_forn_pers`
  ADD PRIMARY KEY (`idContrattoFornPers`);

--
-- Indici per le tabelle `dett_giorni_lav`
--
ALTER TABLE `dett_giorni_lav`
  ADD PRIMARY KEY (`idDettGGLav`),
  ADD KEY `idRichiesta` (`idRichiesta`),
  ADD KEY `idGiorniLav` (`idGiorniLav`);

--
-- Indici per le tabelle `eventi`
--
ALTER TABLE `eventi`
  ADD PRIMARY KEY (`idEvento`);

--
-- Indici per le tabelle `forn_pers`
--
ALTER TABLE `forn_pers`
  ADD PRIMARY KEY (`idFornPers`);

--
-- Indici per le tabelle `forn_pers_old`
--
ALTER TABLE `forn_pers_old`
  ADD PRIMARY KEY (`idFornPers`);

--
-- Indici per le tabelle `foto-par`
--
ALTER TABLE `foto-par`
  ADD PRIMARY KEY (`idFotoPar`);

--
-- Indici per le tabelle `giorni_lav`
--
ALTER TABLE `giorni_lav`
  ADD PRIMARY KEY (`idGiorniLav`),
  ADD KEY `idRichiesta` (`idRichiesta`);

--
-- Indici per le tabelle `guesthost`
--
ALTER TABLE `guesthost`
  ADD PRIMARY KEY (`idGuestHost`);

--
-- Indici per le tabelle `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`idJob`),
  ADD KEY `idRichiesta` (`idRichiesta`),
  ADD KEY `idPersonale` (`idPersonale`);

--
-- Indici per le tabelle `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`idLocation`);

--
-- Indici per le tabelle `locations_bak`
--
ALTER TABLE `locations_bak`
  ADD PRIMARY KEY (`idLocation`);

--
-- Indici per le tabelle `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `mappe`
--
ALTER TABLE `mappe`
  ADD PRIMARY KEY (`idMappa`),
  ADD KEY `Sigla` (`Sigla`);

--
-- Indici per le tabelle `new_pers`
--
ALTER TABLE `new_pers`
  ADD PRIMARY KEY (`idNewPers`);

--
-- Indici per le tabelle `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`idNote`);

--
-- Indici per le tabelle `passaporti`
--
ALTER TABLE `passaporti`
  ADD PRIMARY KEY (`idPass`);

--
-- Indici per le tabelle `personale`
--
ALTER TABLE `personale`
  ADD PRIMARY KEY (`idPersonale`);

--
-- Indici per le tabelle `pers_forn_pers`
--
ALTER TABLE `pers_forn_pers`
  ADD PRIMARY KEY (`idPersFornPers`),
  ADD KEY `idPersonale` (`idPersonale`),
  ADD KEY `idFornPers` (`idFornPers`),
  ADD KEY `idQualifica` (`idQualifica`),
  ADD KEY `idProgetto` (`idProgetto`),
  ADD KEY `Dal` (`Dal`),
  ADD KEY `Al` (`Al`);

--
-- Indici per le tabelle `pers_qual_poss`
--
ALTER TABLE `pers_qual_poss`
  ADD PRIMARY KEY (`idPersQualPoss`);

--
-- Indici per le tabelle `progetti`
--
ALTER TABLE `progetti`
  ADD PRIMARY KEY (`idProgetto`);

--
-- Indici per le tabelle `prog_steps`
--
ALTER TABLE `prog_steps`
  ADD PRIMARY KEY (`idProgStep`),
  ADD KEY `idProgetto` (`idProgetto`),
  ADD KEY `idArea` (`idArea`);

--
-- Indici per le tabelle `prog_steps_req_template`
--
ALTER TABLE `prog_steps_req_template`
  ADD PRIMARY KEY (`idProgStepReqTemplate`);

--
-- Indici per le tabelle `prog_steps_template`
--
ALTER TABLE `prog_steps_template`
  ADD PRIMARY KEY (`idProgStepTemplate`);

--
-- Indici per le tabelle `prog_template`
--
ALTER TABLE `prog_template`
  ADD PRIMARY KEY (`idProgTemplate`);

--
-- Indici per le tabelle `pw_contratti`
--
ALTER TABLE `pw_contratti`
  ADD PRIMARY KEY (`idPwContratto`);

--
-- Indici per le tabelle `qualifiche`
--
ALTER TABLE `qualifiche`
  ADD PRIMARY KEY (`idQualifica`);

--
-- Indici per le tabelle `raggruppamenti`
--
ALTER TABLE `raggruppamenti`
  ADD PRIMARY KEY (`idRagg`),
  ADD KEY `numRagg` (`NumRagg`);

--
-- Indici per le tabelle `richieste`
--
ALTER TABLE `richieste`
  ADD PRIMARY KEY (`idRichiesta`),
  ADD KEY `idProgStep` (`idProgStep`);

--
-- Indici per le tabelle `savetot`
--
ALTER TABLE `savetot`
  ADD PRIMARY KEY (`idSaveTot`);

--
-- Indici per le tabelle `savetot_dett`
--
ALTER TABLE `savetot_dett`
  ADD PRIMARY KEY (`idDettSaveTot`);

--
-- Indici per le tabelle `spesemese`
--
ALTER TABLE `spesemese`
  ADD PRIMARY KEY (`idSpeseMese`),
  ADD KEY `idProgetto` (`idProgetto`);

--
-- Indici per le tabelle `supervisori`
--
ALTER TABLE `supervisori`
  ADD PRIMARY KEY (`idSupervisore`);

--
-- Indici per le tabelle `tab_contr_md`
--
ALTER TABLE `tab_contr_md`
  ADD PRIMARY KEY (`IdTabContrMd`);

--
-- Indici per le tabelle `tab_fatt_gg`
--
ALTER TABLE `tab_fatt_gg`
  ADD PRIMARY KEY (`IdTabFattGG`);

--
-- Indici per le tabelle `tab_fornpers_types`
--
ALTER TABLE `tab_fornpers_types`
  ADD PRIMARY KEY (`idFornpersType`);

--
-- Indici per le tabelle `tab_guests`
--
ALTER TABLE `tab_guests`
  ADD PRIMARY KEY (`idTab_guest`);

--
-- Indici per le tabelle `tab_hosts`
--
ALTER TABLE `tab_hosts`
  ADD PRIMARY KEY (`idTab_host`);

--
-- Indici per le tabelle `tab_ore_gg`
--
ALTER TABLE `tab_ore_gg`
  ADD PRIMARY KEY (`IdTabOreGG`);

--
-- Indici per le tabelle `tab_schemi_lav`
--
ALTER TABLE `tab_schemi_lav`
  ADD PRIMARY KEY (`idTabSchemiLav`);

--
-- Indici per le tabelle `tab_stato_newpers`
--
ALTER TABLE `tab_stato_newpers`
  ADD PRIMARY KEY (`IdTabStatoNewPers`);

--
-- Indici per le tabelle `tab_tipospesa`
--
ALTER TABLE `tab_tipospesa`
  ADD PRIMARY KEY (`idTabTipoSpesa`);

--
-- Indici per le tabelle `tab_tipo_gg`
--
ALTER TABLE `tab_tipo_gg`
  ADD PRIMARY KEY (`IdTabTipoGG`);

--
-- Indici per le tabelle `tab_userlevel`
--
ALTER TABLE `tab_userlevel`
  ADD PRIMARY KEY (`id_userlevel`);

--
-- Indici per le tabelle `tab_valute`
--
ALTER TABLE `tab_valute`
  ADD PRIMARY KEY (`idTabValuta`);

--
-- Indici per le tabelle `tab_visa_types`
--
ALTER TABLE `tab_visa_types`
  ADD PRIMARY KEY (`idVisaType`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id_utente`),
  ADD KEY `id_userlevel` (`id_userlevel`),
  ADD KEY `utente` (`nome`),
  ADD KEY `id_cantiere` (`id_cantiere`),
  ADD KEY `id_impresa` (`id_impresa`);

--
-- Indici per le tabelle `valute`
--
ALTER TABLE `valute`
  ADD PRIMARY KEY (`idValuta`);

--
-- Indici per le tabelle `visti`
--
ALTER TABLE `visti`
  ADD PRIMARY KEY (`idVisto`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `activity`
--
ALTER TABLE `activity`
  MODIFY `idActivity` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `aiuto`
--
ALTER TABLE `aiuto`
  MODIFY `idAiuto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `allegati`
--
ALTER TABLE `allegati`
  MODIFY `idAllegato` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `alleguests`
--
ALTER TABLE `alleguests`
  MODIFY `idAlleGuest` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `aree`
--
ALTER TABLE `aree`
  MODIFY `idArea` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `autorizz_progetti`
--
ALTER TABLE `autorizz_progetti`
  MODIFY `idAutorizzProgetto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `business_units`
--
ALTER TABLE `business_units`
  MODIFY `idBusinessUnit` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `calendars`
--
ALTER TABLE `calendars`
  MODIFY `idCalendar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `clienti`
--
ALTER TABLE `clienti`
  MODIFY `idCliente` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `contratti`
--
ALTER TABLE `contratti`
  MODIFY `idContratto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `contratti_forn_pers`
--
ALTER TABLE `contratti_forn_pers`
  MODIFY `idContrattoFornPers` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `dett_giorni_lav`
--
ALTER TABLE `dett_giorni_lav`
  MODIFY `idDettGGLav` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `eventi`
--
ALTER TABLE `eventi`
  MODIFY `idEvento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `forn_pers`
--
ALTER TABLE `forn_pers`
  MODIFY `idFornPers` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `forn_pers_old`
--
ALTER TABLE `forn_pers_old`
  MODIFY `idFornPers` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `foto-par`
--
ALTER TABLE `foto-par`
  MODIFY `idFotoPar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `giorni_lav`
--
ALTER TABLE `giorni_lav`
  MODIFY `idGiorniLav` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `guesthost`
--
ALTER TABLE `guesthost`
  MODIFY `idGuestHost` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `jobs`
--
ALTER TABLE `jobs`
  MODIFY `idJob` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `locations`
--
ALTER TABLE `locations`
  MODIFY `idLocation` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `locations_bak`
--
ALTER TABLE `locations_bak`
  MODIFY `idLocation` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `login`
--
ALTER TABLE `login`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `mappe`
--
ALTER TABLE `mappe`
  MODIFY `idMappa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `new_pers`
--
ALTER TABLE `new_pers`
  MODIFY `idNewPers` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `note`
--
ALTER TABLE `note`
  MODIFY `idNote` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `passaporti`
--
ALTER TABLE `passaporti`
  MODIFY `idPass` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `personale`
--
ALTER TABLE `personale`
  MODIFY `idPersonale` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `pers_forn_pers`
--
ALTER TABLE `pers_forn_pers`
  MODIFY `idPersFornPers` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `pers_qual_poss`
--
ALTER TABLE `pers_qual_poss`
  MODIFY `idPersQualPoss` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `progetti`
--
ALTER TABLE `progetti`
  MODIFY `idProgetto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `prog_steps`
--
ALTER TABLE `prog_steps`
  MODIFY `idProgStep` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `prog_steps_req_template`
--
ALTER TABLE `prog_steps_req_template`
  MODIFY `idProgStepReqTemplate` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `prog_steps_template`
--
ALTER TABLE `prog_steps_template`
  MODIFY `idProgStepTemplate` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `prog_template`
--
ALTER TABLE `prog_template`
  MODIFY `idProgTemplate` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `pw_contratti`
--
ALTER TABLE `pw_contratti`
  MODIFY `idPwContratto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `qualifiche`
--
ALTER TABLE `qualifiche`
  MODIFY `idQualifica` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `raggruppamenti`
--
ALTER TABLE `raggruppamenti`
  MODIFY `idRagg` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `richieste`
--
ALTER TABLE `richieste`
  MODIFY `idRichiesta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `savetot`
--
ALTER TABLE `savetot`
  MODIFY `idSaveTot` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `savetot_dett`
--
ALTER TABLE `savetot_dett`
  MODIFY `idDettSaveTot` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `spesemese`
--
ALTER TABLE `spesemese`
  MODIFY `idSpeseMese` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `supervisori`
--
ALTER TABLE `supervisori`
  MODIFY `idSupervisore` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_contr_md`
--
ALTER TABLE `tab_contr_md`
  MODIFY `IdTabContrMd` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_fatt_gg`
--
ALTER TABLE `tab_fatt_gg`
  MODIFY `IdTabFattGG` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_fornpers_types`
--
ALTER TABLE `tab_fornpers_types`
  MODIFY `idFornpersType` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_guests`
--
ALTER TABLE `tab_guests`
  MODIFY `idTab_guest` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_hosts`
--
ALTER TABLE `tab_hosts`
  MODIFY `idTab_host` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_ore_gg`
--
ALTER TABLE `tab_ore_gg`
  MODIFY `IdTabOreGG` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_schemi_lav`
--
ALTER TABLE `tab_schemi_lav`
  MODIFY `idTabSchemiLav` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_stato_newpers`
--
ALTER TABLE `tab_stato_newpers`
  MODIFY `IdTabStatoNewPers` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_tipospesa`
--
ALTER TABLE `tab_tipospesa`
  MODIFY `idTabTipoSpesa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_tipo_gg`
--
ALTER TABLE `tab_tipo_gg`
  MODIFY `IdTabTipoGG` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_userlevel`
--
ALTER TABLE `tab_userlevel`
  MODIFY `id_userlevel` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_valute`
--
ALTER TABLE `tab_valute`
  MODIFY `idTabValuta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tab_visa_types`
--
ALTER TABLE `tab_visa_types`
  MODIFY `idVisaType` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id_utente` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `valute`
--
ALTER TABLE `valute`
  MODIFY `idValuta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `visti`
--
ALTER TABLE `visti`
  MODIFY `idVisto` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
