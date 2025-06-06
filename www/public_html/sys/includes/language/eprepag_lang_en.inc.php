<?php require_once __DIR__ . '/../../../../includes/constantes_url.php'; ?>
<?php 

# $Id: eprepag_lang_en.inc.php 
# Copyright (c) 2007-2008, Reinaldo Perez Sanchez EPREPAG_URL
# All rights reserved.  

@define('LANG_NOT', 'does not have');
@define('LANG_CHARSET', 'ISO-8859-1');
@define('LANG_SQL_CHARSET', 'latin1');
@define('LANG_DATE_LOCALES', 'en_US.ISO-8859-1, en_US.ISO8859-1, english, en, en_US');
@define('LANG_DATE_FORMAT_ENTRY', '%A, %B %e. %Y');
@define('LANG_DATE_FORMAT_SHORT', '%m-%d-%Y %H:%M');
@define('LANG_WYSIWYG_LANG', 'en');
@define('LANG_NUMBER_FORMAT_DECIMALS', '2');
@define('LANG_NUMBER_FORMAT_DECPOINT', '.');
@define('LANG_NUMBER_FORMAT_THOUSANDS', ',');
@define('LANG_DIRECTION', 'ltr');
@define('LANG_NAME_CALENDAR_FILE', 'popcalendar.js');

@define('LANG_EPP_ADMIN_SITE_NAME', 'E-Prepag Administration Suite');
@define('LANG_HOME_PAGE_TITLE', 'Home');
@define('LANG_HOME_QUIT', 'Quit');
@define('LANG_HOME_GREETING', 'Welcome');

@define('LANG_TEXT_TITLE_CHARGEBACK', 'Chargeback Report');
@define('LANG_TEXT_BUTTON_SEARCH', 'Search');
@define('LANG_DATE_RETURN', 'Return Date');
@define('LANG_NUM_REQUEST', 'Order Number');
@define('LANG_FORM_RETURN', 'Form of Return');
@define('LANG_FORM_RETURN_OPALL', 'Select all');
@define('LANG_FORM_RETURN_INFO', 'No record was found');
@define('LANG_FORM_RETURN_INFO_ERRO_DATE', 'Enter the date range which is empty');
@define('LANG_FORM_RETURN_OPONE', 'balance return');
@define('LANG_FORM_RETURN_OPTWO', 'Return via deposit');

@define('LANG_HOME_SECTION_PRODUCTS_PINS', 'Products Pins');
@define('LANG_HOME_SECTION_REPORTS_PINS', 'Reports');
@define('LANG_HOME_ITEM_SUPPLY_PINS', 'Stock');
@define('LANG_CHARGEBACK', 'Chargeback Report');
@define('LANG_HOME_ITEM_REPORT_PINS', 'Sales Reports');
@define('LANG_HOME_ITEM_REPORT_PINS_BHN', 'Sales Report BHN');
@define('LANG_HOME_ITEM_REPORT_PINS_BHN_EGIFT', 'Sales Report BHN eGift');
@define('LANG_HOME_ITEM_REPORT_PINS_ONGAME', 'Detailed Sales Report');
@define('LANG_HOME_ITEM_REPORT_PINS_BY_YEAR', 'Sales by Year');
@define('LANG_HOME_ITEM_REPORT_PINS_BY_MONTH', 'Sales by Month');
@define('LANG_HOME_ITEM_REPORT_PINS_BY_DAY', 'Sales by Day');
@define('LANG_HOME_ITEM_REPORT_PINS_BY_DAY_OLD', 'Sales by Day (old)');
@define('LANG_HOME_ITEM_REPORT_PINS_BY_HOUR', 'Sales by Hour');
@define('LANG_HOME_ITEM_REPORT_PINS_BY_ESTABLISHMENT', 'Sales by Establishment');

@define('LANG_HOME_SECTION_PRODUCTS_CARTOES', 'Products Cards');
@define('LANG_HOME_SECTION_REPORTS_CARTOES', 'Reports');
@define('LANG_HOME_SECTION_REPORTS_CARTOES_STR', 'Cards Reports');
@define('LANG_HOME_ITEM_SUPPLY_CARTOES', 'Stock');
@define('LANG_HOME_ITEM_EDIT_CARTOES', 'Edit Sales Registers');
@define('LANG_HOME_ITEM_ATIVACAO_CARTOES', 'Send Card Activation Report');
@define('LANG_HOME_ITEM_EDIT_BOLETO_CARTOES', 'Edit Boletos Registers');
@define('LANG_HOME_ITEM_REPORT_CARTOES', 'Sales Reports');
@define('LANG_HOME_ITEM_REPORT_CARTOES_BY_YEAR', 'Sales by Year');
@define('LANG_HOME_ITEM_REPORT_CARTOES_BY_MONTH', 'Sales by Month');
@define('LANG_HOME_ITEM_REPORT_CARTOES_BY_DAY', 'Sales by Day');
@define('LANG_HOME_ITEM_REPORT_CARTOES_BY_HOUR', 'Sales by Hour');
@define('LANG_HOME_ITEM_REPORT_CARTOES_BY_ESTABLISHMENT', 'Sales by Establishment');

@define('LANG_HOME_SECTION_SALES_LOADS', 'Load Sales Data');
@define('LANG_HOME_SECTION_SALES_STATS', 'Sales Statistics');
@define('LANG_HOME_ITEM_SALES_LOAD_POS', 'Load POS Sale Data');
@define('LANG_HOME_ITEM_SALES_LIST_POS', 'List POS Sale Data');
@define('LANG_HOME_ITEM_SALES_STATS_TOTAL', 'Show Total Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_TOTAL_POR_MES', 'Show Total Sales Statistics by Month');
@define('LANG_HOME_ITEM_SALES_STATS_TOTAL_POR_MES_OLD', 'Show Total Sale Statistics by Month (old)');
@define('LANG_HOME_ITEM_SALES_STATS_TOTAL_POR_MES_PUBLISHERS', 'Show Total Sale Statistics by Month (Publishers)');
@define('LANG_HOME_ITEM_SALES_STATS_COMISS_POR_MES', 'Show Sales Commission Statistics by Month');
@define('LANG_HOME_ITEM_SALES_STATS_TOTAL_POR_DIA', 'Show Total Sales Statistics by Day');

@define('LANG_HOME_ITEM_TAB_STATS', 'Tab stats (Homologation)');
@define('LANG_HOME_ITEM_SALES_STATS_POS', 'Show POS Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_MONEY', 'Show Money Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_MONEY_EXPRESS', 'Show Express Money Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_SITE', 'Show Site Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_LH_MONEY', 'Show LH Money Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_LH_MONEY_EXPRESS', 'Show LH Express Money Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_CARDS', 'Show Cards Sales Statistics');
@define('LANG_HOME_ITEM_SALES_STATS_BCG', 'BCG matrix');
@define('LANG_HOME_ITEM_SALES_STATS_BCG_INCOME', 'Matriz BCG Receita');
@define('LANG_HOME_ITEM_SALES_TOTAL_BY_GAME', 'TOTAIS Por JOGOS');

@define('LANG_SITE_COPYRIGHT', 'E-Prepag Copyright '.date('Y').'. All rights reserved.');

@define('LANG_SITE_DAY_OF_WEEK_MONDAY', 'Mon');
@define('LANG_SITE_DAY_OF_WEEK_TUESDAY', 'Tue');
@define('LANG_SITE_DAY_OF_WEEK_WEDNESDAY', 'Wed');
@define('LANG_SITE_DAY_OF_WEEK_THURSDAY', 'Thu');
@define('LANG_SITE_DAY_OF_WEEK_FRIDAY', 'Fri');
@define('LANG_SITE_DAY_OF_WEEK_SATURDAY', 'Sat');
@define('LANG_SITE_DAY_OF_WEEK_SUNDAY', 'Sun');

@define('LANG_SITE_SEARCH_MSG_1', 'Search processed in ');
@define('LANG_SITE_SEARCH_MSG_2', ' seconds.');

@define('LANG_STOCK_PAGE_TITLE', 'Stock');
@define('LANG_STOCK_LINK_TITLE_1', 'Search Pins Supply');
@define('LANG_STOCK_LINK_TITLE_2', 'Search Pins Status');
@define('LANG_STOCK_LINK_TITLE_3', 'Search Pins');
@define('LANG_STOCK_LINK_TITLE_4', 'Search Status Pins');

@define('LANG_PINS_SEARCH_PINS_SALE', 'Search PINs-Sales');
@define('LANG_PINS_SEARCH_PINS_PQUERY', 'PQuery PINs');
@define('LANG_PINS_SEARCH_PINS_PQUERY_DAY', 'Situation Query day');
@define('LANG_PINS_SEARCH_PINS_SITUATION_QUERY_SALE', 'Situation Query Salles');

@define('LANG_PINS_PAGE_TITLE', 'PIN Status');
@define('LANG_PINS_PAGE_TITLE_REPORT', 'PIN Report');
@define('LANG_PINS_PAGE_TITLE_REPORT_RIOT', 'PIN Report - (RIOT LayOut)');
@define('LANG_PINS_PAGE_TITLE_2', 'Pins');
@define('LANG_PINS_SEARCH_1', 'Search');
@define('LANG_PINS_SEARCH_2', 'Find');
@define('LANG_PINS_OPERATOR', 'Publisher');
@define('LANG_PINS_USER', 'User');
@define('LANG_PINS_ALL_OPERATORS', 'All Publishers');
@define('LANG_PINS_ALL_VALUES', 'All Values');
@define('LANG_PINS_QUANTITY', 'Amount');
@define('LANG_PINS_AVARAGE_DAILY_1', 'Day Average');
@define('LANG_PINS_AVARAGE_DAILY_2', 'Last Week');
@define('LANG_PINS_DURATION', 'Time');
@define('LANG_PINS_FACE_VALUE', 'Face Value (R$)');
@define('LANG_PINS_TOTAL_VALUE', 'Total Value (R$)');
@define('LANG_PINS_TOTAL_COMISSAO', 'Commission (R$)');
@define('LANG_PINS_NO_PINS', 'PINS');
@define('LANG_PINS_NO_PINS_FOUND', 'PIN Not Found');
@define('LANG_PINS_AVARAGE_OF', '');
@define('LANG_PINS_DAYS', 'day average');
@define('LANG_PINS_TIME_1', 'Term');
@define('LANG_PINS_TIME_2', 'Terms');
@define('LANG_PINS_START_DATE', 'Start Date');
@define('LANG_PINS_END_DATE', 'End Date');
@define('LANG_PINS_SERIAL_NUMBER', 'Serial Number');
@define('LANG_PINS_PIN_NUMBER', 'PIN Number');
@define('LANG_PINS_SELECT_OPERATOR', 'Select Operator');
@define('LANG_PINS_ID', 'ID');
@define('LANG_PINS_SALES_DATE', 'Sales Date');
@define('LANG_PINS_CREATE_DATE', 'Created Date');
@define('LANG_PINS_USED_DATE', 'Used Date');
@define('LANG_PINS_VALUE', 'Value (R$)');
@define('LANG_PINS_PRODUCT', 'Products');
@define('LANG_PINS_CHANNEL', 'Channel');
@define('LANG_PINS_TYPE', 'Type');
@define('LANG_PINS_STATUS', 'Status');
@define('LANG_PINS_LAST_STATUS', 'Last Status');
@define('LANG_PINS_MENU_BACK', 'Back To The Menu');
@define('LANG_PINS_IMPORT', 'Importation');
@define('LANG_PINS_ALL', 'All');
@define('LANG_PINS_FORMAT', 'Format');
@define('LANG_PINS_LOT', 'Lot');
@define('LANG_PINS_SERIAL_PIN', 'Serial');
@define('LANG_PINS_CODIGO_PIN', 'Code');
@define('LANG_PINS_CARACTER_PIN', 'Char.');
@define('LANG_PINS_CHANNEL', 'Channel');
@define('LANG_PINS_LIST_ALL_REGISTERS', 'List All Registers');
@define('LANG_PINS_LIST_VG_ID', 'List Request Code');
@define('LANG_PINS_REPORT_TYPE', 'Report Type');
@define('LANG_PINS_OUT', 'Stock outflow');
@define('LANG_PINS_START_DATE', 'Wrong Start Date');
@define('LANG_PINS_END_DATE', 'Wrong Limit Date');
@define('LANG_PINS_COMP_DATE_START_WITH_END', 'Start date is minor of the limit date');
@define('LANG_PINS_DATE', 'Date');
@define('LANG_PINS_QUANTITY_1', 'Amount');
@define('LANG_PINS_SUBTOTAL', 'SUBTOTAL');
@define('LANG_PINS_TOTAL', 'TOTAL');
@define('LANG_PINS_LAST_MSG', 'Note: Values shown in R$');
@define('LANG_PINS_SALES', 'Sales');
@define('LANG_PINS_SALE', 'Sale');
@define('LANG_PINS_PAGE_TITLE_1', 'Sales Report (Site+POS)');
@define('LANG_PINS_PAGE_TITLE_2', 'Sales Report by Year (Site+POS)');
@define('LANG_PINS_PAGE_TITLE_3', 'Sales Report by Month (Site+POS)');
@define('LANG_PINS_PAGE_TITLE_4', 'Sales Report by Day (Site+POS)');
@define('LANG_PINS_ALL_CHANNELS', 'All Channels');
@define('LANG_PINS_ALL_TYPES', 'All Types');
@define('LANG_PINS_SITE_CHANNEL', 'Site = Direct Sales + Indirect Sales');
@define('LANG_PINS_POS_CHANNEL', 'POS = Other Networks');
@define('LANG_PINS_SELECT_PIN_STATUS', 'Select PIN status');
@define('LANG_PINS_SOLD_ALL', 'Sold - ALL');
@define('LANG_PINS_SEARCH_MSG', 'Search done in');
@define('LANG_PINS_SEARCH_MSG_UNIT', 'seconds');
@define('LANG_PINS_TOTAL_VALUE_STOCK', 'Total Value in Supply');
@define('LANG_PINS_TOTAL_DATA_SCREEN', 'Total records on the screen');
@define('LANG_PINS_SUMMARY_LAST', 'Summary of Last');
@define('LANG_PINS_FILE_GENERATE', 'CREATE FILE');

@define('LANG_PINS_STATUS_MSG_0', 'Waiting Clearance');
@define('LANG_PINS_STATUS_MSG_1', 'Available');
@define('LANG_PINS_STATUS_MSG_2', 'On process');
@define('LANG_PINS_STATUS_MSG_3', 'Sold - Direct Sales');
@define('LANG_PINS_STATUS_MSG_4', 'Status ???'); //
@define('LANG_PINS_STATUS_MSG_5', 'Status ???'); //
@define('LANG_PINS_STATUS_MSG_6', 'Sold - E-prepag Network');
@define('LANG_PINS_STATUS_MSG_7', 'Sold - Other Networks');
@define('LANG_PINS_STATUS_MSG_8', 'Used');
@define('LANG_PINS_STATUS_MSG_9', 'Deactivated');

@define('LANG_PINS_SERIAL', 'PIN Serial');

@define('LANG_CARDS_PAGE_TITLE', 'Search Sales Cards');
@define('LANG_CARDS_PAGE_TITLE_2', 'Sales Cards - Insert New Sales Data');
@define('LANG_CARDS_PAGE_TITLE_3', 'Report Of The Cards');
@define('LANG_CARDS_PAGE_TITLE_4', 'List Sales Cards for billet fulfilling');
@define('LANG_CARDS_PAGE_TITLE_5', 'Cards Activation Report ONGAME');
@define('LANG_CARDS_SEARCH_1', 'Search');
@define('LANG_CARDS_SEARCH_2', 'Find');
@define('LANG_CARDS_INSERT_NEW', 'Insert New');
@define('LANG_CARDS_SALES_START_DATE', 'Start Sales Date');
@define('LANG_CARDS_SALES_END_DATE', 'Final Sales Date');
@define('LANG_CARDS_ESTABLISHMENT', 'Establishment');
@define('LANG_CARDS_CHANNEL', 'Channel');
@define('LANG_CARDS_OPERATOR', 'Publisher');
@define('LANG_CARDS_VALUE', 'Value');
@define('LANG_CARDS_TOTAL_VALUE', 'Total Value (R$)');
@define('LANG_CARDS_ALL_USERS', 'All Users');
@define('LANG_CARDS_ALL_CHANNELS', 'All Channels');
@define('LANG_CARDS_ALL_OPERATORS', 'All Publishers');
@define('LANG_CARDS_ALL_VALUES', 'All Values');
@define('LANG_CARDS_DATE', 'DAte');
@define('LANG_CARDS_QUANTITY', 'Amount');
@define('LANG_CARDS_SALES', 'Sales');
@define('LANG_CARDS_SALES_2', 'Sale');
@define('LANG_CARDS_START_END_DATE', 'Start date is minor of the limit date');
@define('LANG_CARDS_START_DATE', 'Wrong Start Date');
@define('LANG_CARDS_END_DATE', 'Wrong Limit Date');
@define('LANG_CARDS_NUMBER', 'Number');
@define('LANG_CARDS_SALESMAN', 'Salesman');
@define('LANG_CARDS_SELECT_SALESMAN', 'Select The Salesman');
@define('LANG_CARDS_USER', 'User');
@define('LANG_CARDS_SELECT_USER', 'Select The User');
@define('LANG_CARDS_NO_USER', 'No Users');
@define('LANG_CARDS_USER_MANAGER', 'User´s Manager');
@define('LANG_CARDS_SALES_DATA', 'Sales´s Data');
@define('LANG_CARDS_QUANTITY_SALES_TOTAL', 'Total amount sales');
@define('LANG_CARDS_FREIGHT', 'Freight');
@define('LANG_CARDS_COMMITEE', 'Commission');
@define('LANG_CARDS_VENCIMENTO', 'Time');
@define('LANG_CARDS_DAYS', 'Days');
@define('LANG_CARDS_ASSET_1', 'Asset');
@define('LANG_CARDS_ASSET_2', 'Asset');
@define('LANG_CARDS_INACTIVE', 'Inactive');
@define('LANG_CARDS_SALES_TOTAL', 'Sales Totals');
@define('LANG_CARDS_NO_COMMITEE', 'No Commission');
@define('LANG_CARDS_NO_COMMITEE_WITH_FREIGHT', 'No commission and with freight');
@define('LANG_CARDS_DATAS', 'Datas of the cards');
@define('LANG_CARDS_START', 'start');
@define('LANG_CARDS_END', 'end');
@define('LANG_CARDS_POSTOFFICE_CODE', 'Postoffice Code');
@define('LANG_CARDS_COMMENTS', 'Comments');
@define('LANG_CARDS_NO_COMMENTS', 'No Comments');
@define('LANG_CARDS_INSERT', 'Insert');
@define('LANG_CARDS_UPDATE', 'Update');
@define('LANG_CARDS_EDIT', 'Edit');
@define('LANG_CARDS_NO_REGISTRATION', 'No Registration');
@define('LANG_CARDS_TIME', 'Time');
@define('LANG_CARDS_REPORT', 'Report');
@define('LANG_CARDS_EMAIL_TO', 'Email To');
@define('LANG_CARDS_EMAIL_BCC', 'Email BCC');
@define('LANG_CARDS_SEND_REPORT', 'Send Report');
@define('LANG_CARDS_NAME', 'Name');
@define('LANG_CARDS_FULL_NAME', 'Full Name');
@define('LANG_CARDS_TOTAL_QUANTITY', 'Total Amount');
@define('LANG_CARDS_MSG_HELP', 'Help: Pass mouse on the fields "Number Heading", name of the "Establishment", "Full Name", "CNPJ" and "Value ($)" to mark automatically the field, use "Ctrl+C" to copy the selected text for clipboard, after, use "Ctrl+V" to past where for necessary .');
@define('LANG_CARDS_SALES_REAL', 'Total Sales');
@define('LANG_CARDS_OUT_COMMISSION_FREIGHT', 'without commission + freight');
@define('LANG_CARDS_REGISTER_MSG', 'The register');
@define('LANG_CARDS_REGISTER_MSG_1', 'modified for \'Deposit\'');
@define('LANG_CARDS_REGISTER_MSG_2',  'modified for \'Sequencia\' with value');
@define('LANG_CARDS_NOT_FOUND_REGISTER_MSG', 'Not Found ID_Seq for the register');
@define('LANG_CARDS_NOT_FOUND_REGISTER_MSG_1', 'Not Found ID_Seq_Max');
@define('LANG_CARDS_START_DATE', 'Start Date');
@define('LANG_CARDS_END_DATE', 'End Date');
@define('LANG_CARDS_TOTAL', 'TOTAL');
@define('LANG_CARDS_SEARCH_MSG', 'Search done in');
@define('LANG_CARDS_SEARCH_MSG_UNIT', 'seconds');
@define('LANG_CARDS_COMMISSION_FREIGHT', 'commission + freight');
@define('LANG_CARDS_PHONE', 'Phone');
@define('LANG_CARDS_SUBTOTAL', 'SUBTOTAL');
@define('LANG_ALL', 'TOTAL');
@define('LANG_CARDS_NUMBER_HEADING', 'Number Heading');

@define('LANG_POS_PAGE_TITLE', 'POS´S Establishments');
@define('LANG_POS_SALES_START_DATE', 'Start Sales Data');
@define('LANG_POS_SALES_END_DATE', 'End Sales Data');
@define('LANG_POS_SEARCH_1', 'Search');
@define('LANG_POS_ESTABLISHMENT', 'Establishment');
@define('LANG_POS_ALL_ESTABLISHMENT', 'All Establishments');
@define('LANG_POS_ESTABLISHMENT_TYPE', 'Type Of The Establishemnt');
@define('LANG_POS_ALL_TYPES', 'All Types');
@define('LANG_POS_CITY', 'City');
@define('LANG_POS_ALL_CITIES', 'All Cities');
@define('LANG_POS_STATE', 'State');
@define('LANG_POS_ALL_STATES', 'All States');
@define('LANG_POS_OPERATOR', 'Publisher');
@define('LANG_POS_ALL_OPERATOR', 'All Publishers');
@define('LANG_POS_VALUE', 'Value');
@define('LANG_POS_ALL_VALUE', 'All Values');
@define('LANG_POS_SEARCH_2', 'Search');
@define('LANG_POS_TYPE', 'Type');
@define('LANG_POS_PHONES', 'Phones');
@define('LANG_POS_SALES_NUMBER', 'Sales Number');
@define('LANG_POS_LAST_SALE', 'Last Sales');
@define('LANG_POS_TOTAL', 'Total');
@define('LANG_POS_STATES', 'State');
@define('LANG_POS_SEARCH_MSG', 'Search done in');
@define('LANG_POS_SEARCH_MSG_UNIT', 'seconds');
@define('LANG_POS_DATE', 'Date');
@define('LANG_POS_PAGE_TITLE', 'List Sales of Establishment of POS');
@define('LANG_POS_INSERT_NEW', 'Insert New');

@define('LANG_REPORTS_PAGE_TITLE', 'Sales Report (Cards)');
@define('LANG_REPORTS_PAGE_TITLE_2', 'Sales Report for Year (Cards)');
@define('LANG_REPORTS_PAGE_TITLE_3', 'Sales Report for Month (Cards)');
@define('LANG_REPORTS_PAGE_TITLE_4', 'Sales Totals');
@define('LANG_REPORTS_PAGE_TITLE_5', 'for Month - Statistics');
@define('LANG_REPORTS_PAGE_TITLE_6', 'Sales Report for Day (Cards)');
@define('LANG_REPORTS_TITLE', 'Totals Sales Statistics');
@define('LANG_REPORTS_SEARCH_1', 'Search');
@define('LANG_REPORTS_SEARCH_2', 'Search');
@define('LANG_REPORTS_SALES_START_DATE', 'Start Sales Date');
@define('LANG_REPORTS_SALES_END_DATE', 'End Sales Date');
@define('LANG_REPORTS_OPERATOR', 'Publisher');
@define('LANG_REPORTS_ALL_OPERATOR', 'All Publishers');
@define('LANG_REPORTS_VALUE', 'Value');
@define('LANG_REPORTS_ALL_VALUE', 'All Values');
@define('LANG_REPORTS_DATE', 'Date');
@define('LANG_REPORTS_QUANTITY', 'Amount');
@define('LANG_REPORTS_FACE_VALUE', 'Face Value');
@define('LANG_REPORTS_TOTAL_VALUE', 'Total Value (R$)');
@define('LANG_REPORTS_START_END_DATE', 'Start Date minor of the End Date');
@define('LANG_REPORTS_START_DATE', 'Wrong Start Date');
@define('LANG_REPORTS_END_DATE', 'Wrong End Date');
@define('LANG_REPORTS_TOTAL', 'Total');
@define('LANG_REPORTS_TOTALS', 'Total');
@define('LANG_REPORTS_LAST_MSG', 'OBS.: Values in R$');
@define('LANG_REPORTS_REPORT_TYPE', 'Report Type');
@define('LANG_REPORTS_SALE', 'Sales');
@define('LANG_REPORTS_OUT', 'Stock outflow');
@define('LANG_REPORTS_CARDS', 'Cards');
@define('LANG_REPORTS_SITE', 'Site');
@define('LANG_REPORTS_SALES', 'Sales');
@define('LANG_REPORTS_PROJECTION', 'Projection');
@define('LANG_REPORTS_LAST_MSG_2', 'Note: The values for projections are calculated by adding the value achieved in the current month and the daily average of the last month days multiplied by the days remaining to complete the current month.');
@define('LANG_REPORTS_MENU_BACK', 'back to menu');
@define('LANG_REPORTS_SEARCH_MSG', 'Search done in');
@define('LANG_REPORTS_SEARCH_MSG_UNIT', 'seconds');
@define('LANG_REPORTS_INTERVAL_DATES_MSG', 'Interval of Dates');
@define('LANG_REPORTS_COMP_DATE_START_WITH_END', 'Start date is minor of the limit date');
@define('LANG_REPORTS_PAGE_TITLE_7', 'Sales Report of Hour (Site+POS)');
@define('LANG_REPORTS_AVERAGE_QUANTITY', 'Average Amount');
@define('LANG_REPORTS_AVERAGE_VALUE', 'Average Value');
@define('LANG_REPORTS_TOTAL_1', 'TOTAL');

@define('LANG_STATISTICS_PAGE_TITLE_1', 'POS´s DATA');
@define('LANG_STATISTICS_PAGE_TITLE_2', 'Statistics');
@define('LANG_STATISTICS_PAGE_TITLE_3', 'Money Data');
@define('LANG_STATISTICS_PAGE_TITLE_4', 'Money Express Data');
@define('LANG_STATISTICS_PAGE_TITLE_5', 'Sales in Site Data');
@define('LANG_STATISTICS_PAGE_TITLE_6', 'LH Money Data');
@define('LANG_STATISTICS_PAGE_TITLE_7', 'Cards Data');
@define('LANG_STATISTICS_TITLE', 'Statistics of POS');
@define('LANG_STATISTICS_TITLE_2', 'Statistics of Money');
@define('LANG_STATISTICS_TITLE_3', 'Statistics of Money Express');
@define('LANG_STATISTICS_TITLE_4', 'Statistics of Sales in Site');
@define('LANG_STATISTICS_TITLE_5', 'Statistics of LH Money');
@define('LANG_STATISTICS_TITLE_6', 'Statistics of Cards');
@define('LANG_STATISTICS_FOR_MONTH', 'for Month');
@define('LANG_STATISTICS_FOR_WEEK_DAY', 'for Day of the week');
@define('LANG_STATISTICS_FOR_DAY', 'for Day');
@define('LANG_STATISTICS_FOR_PUBLISHER', 'for Publisher');
@define('LANG_STATISTICS_FOR_GAME', 'for Game');
@define('LANG_STATISTICS_FOR_STATE', 'for State');
@define('LANG_STATISTICS_FOR_CITY', 'for City');
@define('LANG_STATISTICS_FOR_TYPE', 'for Type of Establishment');
@define('LANG_STATISTICS_FOR_ESTABLISHMENT', 'for Establishment');
@define('LANG_STATISTICS_FOR_LAST_MONTH', 'Last Month');
@define('LANG_STATISTICS_FOR_LAST_WEEK', 'Last Week');
@define('LANG_TOTAL_ESTABLISHMENT', 'Establishment-Total');
@define('LANG_TOTAL_ESTABLISHMENT_MONTH', 'Month-Establishment-Total');
@define('LANG_STATISTICS_FOR_ESTABLISHMENT_TYPE', 'for Type of Establishment');
@define('LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH', 'for Publisher of this Month');
@define('LANG_STATISTICS_FOR_USER', 'for User');
@define('LANG_STATISTICS_FOR_TOTALS', 'for Totals');
@define('LANG_STATISTICS_FOR_GAME_THIS_WEEK', 'for Games of this Week');
@define('LANG_STATISTICS_FOR_GAME_THIS_MONTH', 'for Games of this Month');
@define('LANG_STATISTICS_OUT', 'Stock outflow');
@define('LANG_STATISTICS_OPERATOR', 'Publisher');
@define('LANG_STATISTICS_ALL_OPERATOR', 'All the operators');
@define('LANG_STATISTICS_YEAR', 'Year');
@define('LANG_STATISTICS_ALL_YEAR', 'All Years');
@define('LANG_STATISTICS_TOTAL_FOR_MONTH', 'Total for month');
@define('LANG_STATISTICS_SALES_NUMBER', 'Sales Number');
@define('LANG_STATISTICS_SALES_VALUE', 'Value of sales');
@define('LANG_STATISTICS_VALUE', 'value');
@define('LANG_STATISTICS_TOTAL_FOR_WEEK_DAY', 'Total for Day of the week');
@define('LANG_STATISTICS_DAY_OF_THE_WEEK', 'Day of week');
@define('LANG_STATISTICS_TOTAL_FOR_DAY', 'Total for day');
@define('LANG_STATISTICS_IN', 'in');
@define('LANG_STATISTICS_AVERAGE', 'Average');
@define('LANG_STATISTICS_PROJECTION', 'Projection');
@define('LANG_STATISTICS_TOTAL_FOR_PUBLISHER', 'Total for Publisher');
@define('LANG_STATISTICS_PUBLISHERS', 'publishers');
@define('LANG_STATISTICS_GAME', 'Game');
@define('LANG_STATISTICS_THIS_MONTH', 'this month');
@define('LANG_STATISTICS_TOTAL_FOR_GAME', 'Total for Game');
@define('LANG_STATISTICS_ITEM', 'Item');
@define('LANG_STATISTICS_TOTAL_FOR_STATE', 'Total for State');
@define('LANG_STATISTICS_TOTAL_FOR_CITY', 'Total for City');
@define('LANG_STATISTICS_TOTAL_FOR_TYPE', 'Total for Type de Estabelecimento');
@define('LANG_STATISTICS_TOTAL_FOR_ESTABLISHMENT', 'Total for Establishment');
@define('LANG_STATISTICS_LAST_SALES', 'Last Sale');
@define('LANG_STATISTICS_LAST_MONTH_SINCE', 'Last Week since');
@define('LANG_STATISTICS_LAST_WEEK_SINCE', 'Last Week since');
@define('LANG_STATISTICS_LAST_MONTH', 'Last Month');
@define('LANG_STATISTICS_LAST_WEEK', 'Last Week');
@define('LANG_STATISTICS_TOTAL', 'Total');
@define('LANG_STATISTICS_TOTALS', 'Total');
@define('LANG_STATISTICS_THIS_MONTH', 'this month');
@define('LANG_STATISTICS_STATES', 'States');
@define('LANG_STATISTICS_STATE', 'State');
@define('LANG_STATISTICS_CITIES', 'Cities');
@define('LANG_STATISTICS_CITY', 'City');
@define('LANG_STATISTICS_ESTABLISHMENT_TYPES', 'Types Establishment');
@define('LANG_STATISTICS_ESTABLISHMENT_TYPE', 'Type Establishment');
@define('LANG_STATISTICS_ESTABLISHMENTS', 'Establishments');
@define('LANG_STATISTICS_OF_INSERT', 'in register');
@define('LANG_STATISTICS_ESTABLISHMENT', 'Establishment');
@define('LANG_STATISTICS_TYPE', 'Type');
@define('LANG_STATISTICS_TO', 'to');
@define('LANG_STATISTICS_SALES', 'Sales');
@define('LANG_STATISTICS_REPORT_TYPE', 'Report Type');
@define('LANG_STATISTICS_GAMES', 'Games');
@define('LANG_STATISTICS_USERS', 'Users');
@define('LANG_STATISTICS_USER', 'User');
@define('LANG_STATISTICS_MEDIUM', 'Average');
@define('LANG_STATISTICS_PAGE_TITLE_8', 'Total Sales');
@define('LANG_STATISTICS_TOTAL_SALES', 'Total Sales Statistics');
@define('LANG_STATISTICS_NOT_FOUND_REGISTER_MSG', 'Registers Not Found for POS');
@define('LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_2', 'Registers Not Found from POS');
@define('LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_3', 'Registers Not Found for Money');
@define('LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_4', 'Registers Not Found from Money');
@define('LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5', 'Registers Not Found for Money Express');
@define('LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_6', 'Registers Not Found from Money Express');
@define('LANG_STATISTICS_CHANNEL', 'Channel');
@define('LANG_STATISTICS_NUMBER_DAYS', 'Number Days');
@define('LANG_STATISTICS_NUMBER_FROM', 'Number from');
@define('LANG_STATISTICS_SALES_1', 'sales');
@define('LANG_STATISTICS_AVERAGE_TOTAL', 'Average Total');
@define('LANG_STATISTICS_AVERAGE_EPP', 'Average EPP');
@define('LANG_STATISTICS_PROJECTION_EPP', 'Projection EPP');
@define('LANG_STATISTICS_INFO_MSG', 'Note');
@define('LANG_STATISTICS_INFO_MSG_1', 'The column "30 day estimative" and "Projection EPP in 30 days" uses');
@define('LANG_STATISTICS_INFO_MSG_2', 'The column "30 day estimative" uses');
@define('LANG_STATISTICS_INFO_MSG_3', 'the said value in the month until  and adds the projection for the remaining days from the average of last the 2 months (or less, if there are no enough data).');
@define('LANG_STATISTICS_INFO_MSG_4', 'Note');
@define('LANG_STATISTICS_INFO_MSG_5', 'The values for projections are calculated by adding the value achieved in the current month and the daily average of the last month days multiplied by the days remaining to complete the current month');
@define('LANG_STATISTICS_SEARCH_MSG', 'Searched in');
@define('LANG_STATISTICS_SEARCH_MSG_UNIT', 'seconds');
@define('LANG_STATISTICS_TOTAL_CHANNEL', 'Total for Channel');
@define('LANG_STATISTICS_TOTALS_SALES', 'Totals Sales');
@define('LANG_STATISTICS_REPORT_TYPE', 'Report Type');
@define('LANG_STATISTICS_CARDS', 'Cards');
@define('LANG_STATISTICS_PAGE_TITLE_9', 'for Month - Statistics');
@define('LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1', 'for Publisher This Month');
@define('LANG_STATISTICS_IN_VALUE', 'in value');
@define('LANG_STATISTICS_FOR_ESTABLISHMENT', 'for Establishment');
@define('LANG_STATISTICS_OF_REGISTER', 'of register');
@define('LANG_STATISTICS_SALES_2', 'Sales');
@define('LANG_STATISTICS_GAME_1', 'game');
@define('LANG_STATISTICS_NUMBER_OF', 'Number Of');
@define('LANG_STATISTICS_NUMBER_FROM', 'Qty');
@define('LANG_STATISTICS_TITLE_STATUS_1', 'Frequent');
@define('LANG_STATISTICS_TITLE_STATUS_2', 'Been slow');
@define('LANG_STATISTICS_TITLE_STATUS_3', 'Abandoned');

@define('LANG_PINS_PAGE_TITLE_TRANSFER', 'Modify PIN Channel');
@define('LANG_PINS_AMOUNT', 'Quantity');
@define('LANG_PINS_CURRENT_SITUATION', 'Current situation before modifying PIN channel');
@define('LANG_PINS_BUTTON_ALTER', 'Modify');
@define('LANG_CARDS_CHANNEL_TO', 'Channel Destination');

@define('LANG_COMMISSIONS_TITLE', 'Commissions of Sales');
@define('LANG_COMMISSIONS_PAGE_TITLE', 'Total commissions of Sales');
@define('LANG_COMMISSIONS_PAGE_TITLE_1', 'Statistics');
@define('LANG_COMMISSIONS_INFO_MSG_1', 'Note');
@define('LANG_COMMISSIONS_INFO_MSG_2', 'The values for projections are calculated by adding the value achieved in the current month and daily the average of the last months days multiplied by the days remaining to complete the current month');
@define('LANG_COMMISSIONS_SALES', 'commission');
@define('LANG_COMMISSIONS_TOTAL', 'Total');
@define('LANG_COMMISSIONS_TOTALS', 'Totals');
@define('LANG_COMMISSIONS_TOTAL_SALES', 'sale');
@define('LANG_COMMISSIONS_TOTAL_TRANSFER', 'Transfer');
@define('LANG_COMMISSIONS_PRODUCTS_REGISTERED', 'Registered in cadastre products');
@define('LANG_COMMISSIONS_WITHOUT_REGISTERED', 'without registered in cadastre Commission');
@define('LANG_COMMISSIONS_ALL_PRODUCTS_REGISTERED', 'All the registered in cadastre products');
@define('LANG_COMMISSIONS_HAVE_REGISTERED', 'they have registered in cadastre Commission');
@define('LANG_COMMISSIONS_DB_ERROR_MSG', 'Database Error');
@define('LANG_COMMISSIONS_CARDS', 'Cards');

@define('LANG_COMMISSIONS_MAKE_GRAPHIC', 'Create Graph');
@define('LANG_COMMISSIONS_GRAPHIC_BAR', 'Bar Graph');
@define('LANG_COMMISSIONS_GRAPHIC_TOP_GRACE', 'Top Line Graph');
@define('LANG_COMMISSIONS_GRAPHIC_LINE_PLOT', 'Line Graph');
@define('LANG_COMMISSIONS_GRAPHIC_MULTI_LINES', 'Multi-Line Graph');
@define('LANG_COMMISSIONS_PERIOD', 'Time interval');
@define('LANG_COMMISSIONS_PERIOD_MONTH', 'Monthly');
@define('LANG_COMMISSIONS_PERIOD_FORTNIGHTLY', 'Quarterly');
@define('LANG_COMMISSIONS_PERIOD_WEEK', 'Weekly');
@define('LANG_COMMISSIONS_FINANCIAL_MODULE', 'Financial Module');
@define('LANG_COMMISSIONS_FINANCIAL_CLOSE', 'Closings');
@define('LANG_COMMISSIONS_FINANCIAL_CLOSE_VARIABLE', 'Closings Variable Comiss');
@define('LANG_COMMISSIONS_FINANCIAL_INTEGRATION', 'List Integration orders');
@define('LANG_COMMISSIONS_FINANCIAL_SEARCH_INTEGRATION', 'Search integration');
@define('LANG_COMMISSIONS_FINANCIAL_TITLE', 'Financial Closings');
@define('LANG_COMMISSIONS_FINANCIAL_PAGE_TITLE', 'Financial Closings');
@define('LANG_COMMISSIONS_FINANCIAL_NFSE', 'Create NFes');
@define('LANG_COMMISSIONS_FINANCIAL_NFSE_VARIABLE', 'Create NFes Variable');

//Constantes para Integracao
@define('LANG_INTEGRATION_TITLE_PAGE', 'Search Requests Integration Partners');
@define('LANG_INTEGRATION_REQUESTS', 'Integration Sales Details');
@define('LANG_INTEGRATION_SEARCH', 'Search');
@define('LANG_INTEGRATION_DATE_INCLUDE', 'Inclusion Date');
@define('LANG_INTEGRATION_DATE_CONFIRM', 'Confirmation Date');
@define('LANG_INTEGRATION_DATE_CONCILIATION', 'Conciliation Date');
@define('LANG_INTEGRATION_REQUEST_NUMBER', 'EPP Sale ID');
@define('LANG_INTEGRATION_ORDER_NUMBER', 'Order ID');
@define('LANG_INTEGRATION_EMAIL_CLIENT', 'User e-mail');
@define('LANG_INTEGRATION_CONFIRMED', 'Confirmed');
@define('LANG_INTEGRATION_YES', 'YES');
@define('LANG_INTEGRATION_PAYMENT', 'Payment');
@define('LANG_INTEGRATION_PARTNER', 'Partner');
@define('LANG_INTEGRATION_SALE', 'Sale');
@define('LANG_INTEGRATION_VALUES', 'Values');
@define('LANG_INTEGRATION_SELECT', 'Select');
@define('LANG_INTEGRATION_STATUS_SALE', 'Status Sale');
@define('LANG_INTEGRATION_RECORD', 'Record');
@define('LANG_INTEGRATION_ALL_FORMS_PAYMENT', 'All forms of online payment');
@define('LANG_INTEGRATION_DEPOSIT_AND_BILLET', 'Deposit and Billet (not online)');
@define('LANG_INTEGRATION_SHOW_RESULTS', 'Showing results');
@define('LANG_INTEGRATION_UNTIL', 'to');
@define('LANG_INTEGRATION_BY', 'from');
@define('LANG_INTEGRATION_USER', 'User');
@define('LANG_INTEGRATION_LAST_STATUS', 'Last Status');
@define('LANG_INTEGRATION_TYPE_PAYMENT', 'Type of Payment');
@define('LANG_INTEGRATION_HISTORICAL_RECORD_UPDATES', 'Historical record updates');

//COMMONS
//DAYS
@define('LANG_MONDAY','Monday');
@define('LANG_TUESDAY','Tuesday');
@define('LANG_WEDNESDAY','Wednesday');
@define('LANG_THURSDAY','Thursday');
@define('LANG_FRIDAY','Friday');
@define('LANG_SATURDAY','Saturday');
@define('LANG_SUNDAY','Sunday');

//MONTHS
@define('LANG_JANUARY', 'January');
@define('LANG_FEBRUARY', 'February');
@define('LANG_MARCH', 'March');
@define('LANG_APRIL', 'April');
@define('LANG_MAY', 'May');
@define('LANG_JUNE', 'June');
@define('LANG_JULY', 'July');
@define('LANG_AUGUST', 'August');
@define('LANG_SEPTEMBER', 'September');
@define('LANG_OCTOBER', 'October');
@define('LANG_NOVEMBER', 'November');
@define('LANG_DECEMBER', 'December');

//SHOW DATAS
@define('LANG_SHOW_DATA', 'Showing results');
@define('LANG_NO_DATA', 'There are no registers');
@define('LANG_SHOW', 'Showing');
@define('LANG_DATA', 'Results');
@define('LANG_NOT_FOUND', 'Not Found');
@define('LANG_NOT_FOUND_2', 'Registers Not Found');
@define('LANG_TO', 'to');
@define('LANG_FROM', 'from');

//MONEY
@define('LANG_MONEY', 'R$');

//TIME
@define('LANG_SECONDS', 'seconds');
@define('LANG_MINUTES', 'minutes');
@define('LANG_HOURS', 'hours');
@define('LANG_DAYS', 'days');
@define('LANG_DAY', 'day');
@define('LANG_DAY_2', 'Day');
@define('LANG_WEEKDAY_2', 'Weekday');
@define('LANG_MONTHS', 'months');
@define('LANG_MONTH', 'month');
@define('LANG_MONTH_2', 'Month');
@define('LANG_YEAR', 'year');
@define('LANG_YEAR_2', 'Year');

//OTHERS
@define('LANG_BACK', 'back');
@define('LANG_SELECT', 'Select');

//COLUNAS RELATORIOS
@define('LANG_CITY','City');
@define('LANG_STATE','State');
@define('LANG_NUMBER_SALES','N. Sales');
@define('LANG_VALUE_SALES','Sales Values (R$)&nbsp;&nbsp;&nbsp;&nbsp;');
@define('LANG_VALUE','% Value');
@define('LANG_DAY','Day');
@define('LANG_WEEK','Week');
@define('LANG_ESTABLISHMENT','Establishment');
@define('LANG_TYPE','Type');
@define('LANG_FIRST_LAST','1st-Last Sale');
@define('LANG_UF','UF&nbsp;&nbsp;&nbsp;&nbsp;');
@define('LANG_GAME','Game');
@define('LANG_ITEM','Item');
@define('LANG_MONTH','Month');
@define('LANG_TYPE','Type');
@define('LANG_ESTABLISHMENT_TYPE', 'Type of Establishment');
@define('LANG_USER','User');
@define('LANG_ABANDON','Give up&nbsp;&nbsp;&nbsp;&nbsp;');
@define('LANG_START_DATE','Start Date&nbsp;&nbsp;&nbsp;&nbsp;');
@define('LANG_LAST_DATE','Last Date&nbsp;&nbsp;&nbsp;&nbsp;');
@define('LANG_INTEGRATION','Integration');

//ADMINISTRAÇÃ DE USUÁRIOS
@define('LANG_USER_ADMIN_MODULE', 'User admin');
@define('LANG_CHANGE_PASSWORD', 'Change password');
@define('LANG_SUCCESS_CHANGE_PASS', 'Password changed');
@define('LANG_ERROR_CHANGE_PASS', 'There´s an error for changing password');
@define('LANG_INCORRECT_DATA_WRITED', 'There are fields that may be filled with mistakes. Please, check instructions or contact support');
@define('LANG_CONTACT_SUPPORT', 'Please contact support');
@define('LANG_WRONG_PASSWORD', 'Current password is wrong');
@define('LANG_WRONG_DATA', 'Wrong data');
@define('LANG_CLICK_TO_ACCES_NEW_PASS', 'Click here to login with new password');
@define('LANG_CURRENT_PASS', 'Current password');
@define('LANG_NEW_PASS', 'New password');
@define('LANG_CONFIRM_NEW_PASS', 'Check the new password');
@define('LANG_ACCES_DENIED', 'Access Denied');
@define('LANG_WRONG_CONFIRM_PASS', 'Password checking is wrong');
@define('LANG_MIN_MAX_PASS','*Your password must have from %s until %s characters, letters, numbers and punctuation (|,!,?,*,$, etc)');
@define('LANG_PASSW_NOT_SECURITY','The password doesn´t achieve the security requirements. Please check password instructions');

//página de login
@define('LANG_PASSW',"Password");
@define('LANG_SEND',"Send");
@define('LANG_ACCESS_RESTRICT','Restricted area for authorized users');
@define('LANG_DOUBTS_CONTACT','Contact us if you have any doubt<br> or for further information');
@define('LANG_VERSION','Version');
@define('LANG_EPP_REPORT','E-Prepag Report');

@define('LANG_USER_PASS_INVALID','Invalid password or user');
@define('LANG_ACCESS_DENIED_BACKOFFICE','This user is not allowed to access this area');
@define('LANG_SESSION_EXPIRED','Session expired');
@define('LANG_WRITE_FIELDS','Fill the following fields');
@define('LANG_PASSWORD_MUST_HAVE','Password must have at least %s characters');
@define('LANG_SEND_RPS_TO_PUBLISHER','Send RPS to Publisher');
@define('LANG_MAP_PDV','PDV Map');
@define('LANG_MAP_PDV_TITLE','PDV Map');
@define('LANG_MAP_PDV_SEARCH_1', 'Search');
@define('LANG_MAP_PDV_SEARCH_2', 'Find');

@define('LANG_PUBLISHER_CPF_REPORT','Publisher CPF Report (Bom Sucesso)');
@define('LANG_PUBLISHER_BACEN_REPORT','Publisher BACEN Report (ACAM220)');

@define('LANG_PINS_SOLD_USED', 'Sold or Used - All');

@define('LANG_PINS_CALIFORNIA_TIME', 'Consider California time to create report');

@define('LANG_PINS_CREATE_FILE', 'CREATE FILE');

@define('LANG_PINS_HOPE', 'Wait');

@define('LANG_PINS_CLICK_HERE_TO_DOWNLOAD', 'Click here to download report');

@define('LANG_PINS_TRANSACTION_TYPE', 'Transaction Type');

$finantial_report = array (
    'DIRECT' => 'Direct',
    'INDIRECT'=>'Indirect',
    'SALES'=>'SALES',
    'CARDS'=>'CARDS',
    'SITE'=>'SITE',
    'PDV'=>'LAN',
    'POS'=>'POS',
    'aNVendasAux'=>'Transactions Qty',
    'aVendasAux'=>'Transactions Amount',
    'aNVendasEComisAux'=>'Commission Qty',
    'aVendasEComisAux'=>'Commission Amount',
    'IOF'=>'Tax',
    'IRRF'=>'Tax IRRF',
    'Repasse'=>'Net Wired Payout',
    'GRAND_TOTAL'=>'TOTAL'
    );

