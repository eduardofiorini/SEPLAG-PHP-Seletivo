<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/swagger-ui/swagger-ui.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/swagger-ui/index.css') ?>" />
    <link rel="icon" type="image/png" href="<?= base_url('assets/swagger-ui/favicon-32x32.png') ?>" sizes="32x32" />
    <link rel="icon" type="image/png" href="<?= base_url('assets/swagger-ui/favicon-16x16.png') ?>" sizes="16x16" />
</head>

<body>
<div id="swagger-ui"></div>
<script src="<?= base_url('assets/swagger-ui/swagger-ui-bundle.js') ?>" charset="UTF-8"> </script>
<script src="<?= base_url('assets/swagger-ui/swagger-ui-standalone-preset.js') ?>" charset="UTF-8"> </script>
<script>
    window.onload = function() {
        window.ui = SwaggerUIBundle({
            url: "<?= base_url('swagger.json') ?>",
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
        });
    };
</script>
</body>
</html>