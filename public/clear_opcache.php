<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OpCache reset successfully.";
} else {
    echo "OpCache not enabled or not supported.";
}
