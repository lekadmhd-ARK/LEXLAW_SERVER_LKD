<?php
require __DIR__.'/vendor/autoload.php';
\$app = require_once __DIR__.'/bootstrap/app.php';
\$kernel = \$app->make(\Illuminate\Contracts\Http\Kernel::class);
\$response = \$kernel->handle(
    \Illuminate\Http\Request::create('/billing2', 'GET')
);
echo \$response->getStatusCode();
echo "\n";
echo \$response->getContent() ? substr(\$response->getContent(), 0, 500) : 'EMPTY';
