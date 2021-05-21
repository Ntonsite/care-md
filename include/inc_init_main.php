<?php
# This is the database name
#$dbname = 'caredb_kibongoto';
$dbname = 'caredb';
$weberp_db='weberp_malolo';
# Database user name, default is root or httpd for mysql, or postgres for postgresql
$dbusername = 'hmis';
# Database user password, default is empty char
$dbpassword = '1qaz@WSX';
# Database host name, default = localhost
$dbhost = 'localhost';


# Hospitals Logo Filename in directory gui/img/common/default/
$hospital_logo = 'malolo.jpg';

#NHIF Service Credentials
#For use when integrating wiht NHIF Restful Service
#

/*
$nhif_base = 'https://verification.nhif.or.tz/NHIFService';
$nhif_claim_server ='https://verification.nhif.or.tz/claimsserver';
$nhif_claim_url = 'https://verification.nhif.or.tz/claimsserver/api/v1/Packages/GetPricePackage';

$claims_token_url = 'https://verification.nhif.or.tz/ClaimsServer/Token';
$claims_api_url = 'https://verification.nhif.or.tz/ClaimsServer/api/v1/Claims/SubmitFolios';
$claim_reconciation='https:/verification.nhif.or.tz/claimsServer/api/v1/claims/getSubmittedCl
aims';
*/



//$nhif_base = 'http://196.13.105.15/nhifservice';


$nhif_base = 'https://verification.nhif.or.tz/NHIFService';
$nhif_claim_server ='http://196.13.105.15/claimsserver';
$nhif_claim_url = 'http://196.13.105.15/claimsserver/api/v1/Packages/GetPricePackage';

$claims_token_url = 'http://196.13.105.15/ClaimsServer/Token';

$claims_api_url = 'http://196.13.105.15/ClaimsServer/api/v1/Claims/SubmitFolios';






#Service username
//$nhif_user = 'integrationuser';
$nhif_user = 'smakunga';
#NHIF service password
//$nhif_pwd = 'nhif@2018';
$nhif_pwd = '2710sheyz';

#WebERP REST Service Credentials
#For use when integrating wiht webERP Restful Service
#

#$dcmtk_path = '/usr/local/dcmtk/3.6.0/bin';
$dcmtk_path= '/usr/bin/';





# First key used for simple chaining protection of scripts
$key = '2.67452802362E+28';

# Second key used for accessing modules
$key_2level = '2.48431445375E+26';

# 3rd key for encrypting cookie information
$key_login = '1.69264361013E+27';

# Main host address or domain
$main_domain = '';

# Host address for images
$fotoserver_ip = 'localhost';

# Transfer protocol. Use https if this runs on SSL server
$httprotocol = 'http';

# Set this to your database type. For details refer to ADODB manual or goto http://php.weblogs.com/ADODB/
$dbtype = 'mysqli';
