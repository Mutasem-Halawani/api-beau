<?php
# Clients
$container['AddClientCtrl'] = function () use ($container) {
	return new \Lib\Controllers\AddClientCtrl($container);
};

# CustomersList
$container['CustomersList'] = function () use ($container) {
	return new \Lib\Controllers\CustomersList($container);
};

# GroupsCtrl
$container['GroupsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\GroupsCtrl($container);
};

# CustomersDetails
$container['CustomersDetails\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\ClientsCtrl($container);
};
$container['CustomersDetails\DeptCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\DeptCtrl($container);
};
$container['CustomersDetails\PunchCardsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\PunchCardsCtrl($container);
};
$container['CustomersDetails\NotesCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\NotesCtrl($container);
};
$container['CustomersDetails\MediaCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\MediaCtrl($container);
};
$container['CustomersDetails\SocialCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\SocialCtrl($container);
};
$container['CustomersDetails\SignatureCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\SignatureCtrl($container);
};
$container['CustomersDetails\TimelineCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\TimelineCtrl($container);
};

# CreatingAppointment
$container['CreatingAppointment\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CreatingAppointment\ClientsCtrl($container);
};
# Catalog
$container['ServicesCtrl'] = function () use ($container) {
	return new \Lib\Controllers\ServicesCtrl($container);
};
# Appointments
$container['AppointmentsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\AppointmentsCtrl($container);
};

# Workers
$container['WorkersCtrl'] = function () use ($container) {
	return new \Lib\Controllers\WorkersCtrl($container);
};

# Reminders
$container['RemindersCtrl'] = function () use ($container) {
	return new \Lib\Controllers\RemindersCtrl($container);
};

# Templates
$container['TemplatesCtrl'] = function () use ($container) {
	return new \Lib\Controllers\TemplatesCtrl($container);
};
