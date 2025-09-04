<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TailwindCSS Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-10">

    <div class="max-w-lg mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-3xl font-bold text-blue-600 mb-4">TailwindCSS is Working!</h1>
        <p class="text-gray-700 mb-6">If you see colors and spacing, TailwindCSS is configured correctly.</p>

        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
            Test Button
        </button>
    </div>

</body>
</html>
