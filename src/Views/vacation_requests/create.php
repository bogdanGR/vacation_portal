<?php

$title = "New Vacation Request";
$action = "/employee/requests/store";

$submitLabel = "Submit Request";
$request = $request ?? new \App\Models\VacationRequest([]);
include __DIR__ . '/_form.php';