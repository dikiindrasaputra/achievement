<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title>SPX Achievement Tracker</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <style>
            body { font-family: 'Figtree', sans-serif; }
        </style>
    </head>
    <body class="bg-gray-50 min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md px-4">
            <?php echo e($slot); ?>

        </div>
    </body>
</html>
<?php /**PATH /home/dikii/kerja/achievement/achievement/resources/views/layouts/guest.blade.php ENDPATH**/ ?>