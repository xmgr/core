<?php
    
    use Xmgr\View;
    
    /**
     * Returns the path to the storage directory or concatenates it with a given path.
     *
     * @param string $to (optional) The path to be concatenated with the storage directory.
     *
     * @return string The path to the storage directory or the concatenated path if $to is provided.
     */
    function storage_path(string $to = ''): string {
        return path(env('STORAGE_DIR', 'storage'), $to);
    }
    
    /**
     * Retrieves the path to the CSS directory.
     *
     * @param string $to The path to a specific file or directory within the CSS directory (optional)
     *
     * @return string The absolute path to the CSS directory or a specific file/directory within it
     */
    function css_dir(string $to = ''): string {
        return web_path(joinpaths((string)config('path.css', 'css'), $to));
    }
    
    /**
     * Retrieves the path to the JavaScript directory.
     *
     * @param string $to The path to a specific file or directory within the JavaScript directory (optional)
     *
     * @return string The absolute path to the JavaScript directory or a specific file or directory within it
     */
    function js_dir(string $to = ''): string {
        return web_path(joinpaths((string)config('path.js', 'js'), $to));
    }
    
    /**
     * Retrieves the absolute path to a file or directory within the web root directory.
     *
     * @param string $to The path to a specific file or directory within the web root directory (optional)
     *
     * @return string The absolute path to the file or directory within the web root directory
     */
    function web_path(string $to = ''): string {
        return joinpaths(WEB_ROOT, $to);
    }
    
    # -- View / Templates
    /**
     * Returns the contents of a view file.
     *
     * @param string $path The path to the view file.
     *
     * @return string The contents of the view file if it exists, otherwise an empty string.
     * @throws Exception
     * @deprecated
     */
    function old_view(string $path, $data = []): string {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);
        $file = path("resources/views/$path.php");
        if (!file_exists($file)) {
            $file = path("resources/$path.php");
        }
        if (file_exists($file)) {
            ob_start();
            extract($data);
            require $file;
            $content = ob_get_contents();
            if (str_starts_with($content, '@layout:')) {
                $matches = [];
                preg_match('/^@layout:(.+)/', $content, $matches);
                if (isset($matches[1])) {
                    $layout     = $matches[1];
                    $content    = preg_replace('/@layout:.+/', '', $content);
                    $layoutView = old_view('layouts/' . $layout);
                    $content    = trim(str_replace('@content', $content, $layoutView));
                }
            }
            ob_end_clean();
            $t = str_passwd();
            session(['_token' => $t]);
            $content = str_replace('@csrf', '<input type="hidden" name="_token" value="' . $t . '">', $content);
            $content = preg_replace('/@method.+?\)/ui', '', $content);
            
            return $content;
        }
        
        return '';
    }
    
    /**
     * Creates a new view instance.
     *
     * @param string           $name   The name or path of the view file
     * @param array            $data   The data to pass to the view (optional)
     * @param string|bool|null $layout The name or path of the layout file (optional)
     *
     * @return View An instance of the \Xmgr\View class
     */
    function view(string $name, null|string|bool $layout = true, array $data = []): View {
        return new View($name, $data, $layout);
    }
    
    /**
     * Renders a view script with optional data.
     *
     * @param string $name The name of the view script to render
     * @param array  $data The data to pass to the view script (optional)
     *
     * @return View
     */
    function script(string $name, array $data = []) {
        return view($name, '', $data);
    }
