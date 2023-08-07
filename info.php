<?php
include_once('php/config.php');
include_once('php/pdf.php');
try {
    phpinfo();
} catch (Exception $e) {
    echo $e->getMessage();
}
