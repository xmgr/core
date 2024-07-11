<?php
    
    return [
        
        # NOTE:
        # Configuration files (like the one you're reading right now) reside in the `config/` directory
        # right inside your project root. However, you can change that by setting the `CONFIG_DIR` value in
        # your local `.env` file.
        
        # -- The following configurations are relative to the APP_ROOT (your project root)
        
        # Path the system will search for views
        'views'      => env('VIEWS_DIR', '/resources/views'),
        
        # Path the system will search for layout files
        'layouts'    => env('LAYOUTS_DIR', '/resources/layouts'),
        
        # Path for components
        'components' => env('COMPONENTS_DIR', '/resources/components'),
        
        # This is where the files are stored that define your routes
        'routes'     => env('ROUTES_DIR', '/routes'),
        
        
        # -- The following configurations are relative to the WEB_ROOT (the document root)
        
        # The css directory that is publicly accessible
        'css'        => env('CSS_DIR', 'css'),
    
    ];
