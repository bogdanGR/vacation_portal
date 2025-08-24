<?php
$title = "Edit Vacation Request";
$action = "/employee/requests/" . (int)$request->getId() . "/edit";
$submitLabel = "Save Changes";

include __DIR__ . '/_form.php';
