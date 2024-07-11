<?php
    
    namespace Xmgr;
    
    use Stringable;
    use Xmgr\Filesystem\File;
    
    /**
     * Class View
     *
     * The View class provides functionality for rendering views and layouts.
     */
    class View implements Stringable {
        
        protected array  $data   = [];
        protected string $layout = '';
        protected string $file   = '';
        
        /**
         * Constructor for the class.
         *
         * @param string           $location The location string (optional, default is an empty string).
         * @param array            $data     The data array (optional, default is an empty array).
         * @param string|true|null $layout   The layout.
         *                                   NULL means no layout.
         *                                   TRUE means the default layout is used.
         */
        public function __construct(string $location = '', array $data = [], null|string|bool $layout = true) {
            $this->set($location);
            $this->setLayout($layout);
            $this->with($data);
        }
        
        /**
         * Returns the default layout name.
         *
         * @return mixed The default layout name as defined in the configuration setting 'view.layout.default'.
         */
        final public static function defaultLayoutName(): mixed {
            return config('view.layout.default');
        }
        
        /**
         * Checks if a file exists.
         *
         * @return bool Returns true if the file exists, false otherwise.
         */
        public function exists(): bool {
            return $this->file != '' && File::exists($this->file);
        }
        
        /**
         * Generate the full path for a given view name.
         *
         * @param string ...$view_names
         *
         * @return string The full path for the given view name.
         */
        public static function path(string ...$view_names): string {
            return path(config('path.views'), str_replace('.', DIRECTORY_SEPARATOR, implode('.', $view_names)) . '.php');
        }
        
        /**
         * Generate the full path for a given view name.
         *
         * @param string ...$view_names
         *
         * @return string The full path for the given view name.
         */
        public static function layout_path(string ...$view_names): string {
            return path(config('path.layouts'), str_replace('.', DIRECTORY_SEPARATOR, implode('.', $view_names)) . '.php');
        }
        
        /**
         * Set the view file for rendering.
         *
         * @param string $view_name The name of the view.
         *
         * @return self Returns the current instance of the class.
         */
        public function set(string $view_name): static {
            $view_name = str_collapse(trim($view_name, './'), './');
            $file      = static::path($view_name);
            if (file_exists($file)) {
                $this->setFile($file);
            } else {
                if (env('VIEW_INDEX_FALLBACK', true)) {
                    $file = static::path($view_name . '.index');
                    if (file_exists($file)) {
                        $this->setFile($file);
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Set the layout string for the class.
         *
         * @param string|bool|null $layout The layout string.
         *
         * @return $this Returns the instance of the class.
         */
        public function setLayout(null|string|bool $layout = ''): static {
            if ($layout === null || $layout === false) {
                return $this;
            }
            if ($layout === true) {
                $layout = self::defaultLayoutName();
            }
            $files = [
                static::layout_path($layout),
                static::layout_path($layout . '.index'),
            ];
            foreach ($files as $file) {
                if (File::exists($file)) {
                    $this->layout = $file;
                    break;
                }
            }
            
            return $this;
        }
        
        /**
         * Sets the layout property to the specified layout or an empty string.
         *
         * @param null|string|bool $layout [optional] The layout to be set. Default is an empty string.
         *
         * @return static Returns the current instance of the class for method chaining.
         */
        public function useLayout(null|string|bool $layout = ''): static {
            return $this->setLayout($layout);
        }
        
        /**
         * Loads a file relatively to the calling view file
         *
         * @param string $name
         *
         * @return void
         */
        public function import(string $name) {
            $name = str_replace('.', DIRECTORY_SEPARATOR, $name);
            $file = joinpaths(dirname($this->file), $name . '.php');
            if (File::exists($file)) {
                require $file;
            }
        }
        
        /**
         * Loads a component snippet
         *
         * @param string $name
         *
         * @return void
         */
        public function component(string $name) {
            $file = path(config('path.components'), str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php');
            if (File::exists($file)) {
                require $file;
            }
        }
        
        /**
         * Sets the layout property to true, indicating that the default layout should be used.
         *
         * @return $this Returns the current instance of the class for method chaining.
         */
        public function useDefaultLayout() {
            return $this->setLayout(true);
        }
        
        /**
         * Sets the layout property to an empty string.
         *
         * @return $this Returns the current instance of the class for method chaining.
         */
        public function noLayout() {
            $this->layout = '';
            
            return $this;
        }
        
        /**
         * Sets the file for the object.
         *
         * @param string $file The file path.
         *
         * @return $this
         */
        public function setFile(string $file): static {
            if (file_exists($file)) {
                $this->file = $file;
            }
            
            return $this;
        }
        
        /**
         * Parses a file and returns its content as a string.
         *
         * @param string $file The path to the file to be parsed.
         *
         * @return string The content of the parsed file, or an empty string if the file does not exist or cannot be
         *                parsed.
         */
        public function parse(string $file, array $data = []): string {
            if (File::exists($file)) {
                ob_start();
                $this->with($data);
                extract($this->data);
                require $file;
                $tmp = ob_get_contents();
                ob_end_clean();
                if (is_string($tmp)) {
                    return $tmp;
                }
            }
            
            return '';
        }
        
        /**
         * Updates the data array with the given data and returns the modified instance.
         *
         * @param array $data The data array to merge with the existing data.
         *
         * @return $this
         */
        public function with(array $data): static {
            $this->data = array_replace_recursive($this->data, $data);
            
            return $this;
        }
        
        /**
         * Returns the HTML content of a component.
         *
         * @param string $name The name of the component to render.
         *
         * @return string The HTML content of the component.
         */
        public function template(string $name): string {
            $file = static::path('templates.' . $name);
            if (File::exists($file)) {
                return $this->parse($file);
            }
            
            return '';
        }
        
        /**
         * Loads a view and parses it with the given data.
         *
         * @param string $view The name or path of the view to load.
         * @param array  $data The optional data to pass to the view.
         *
         * @return string The parsed view content.
         */
        public function load(string $view, array $data = []): string {
            return $this->parse(static::path($view), $data);
        }
        
        /**
         * Renders the layout.
         *
         * @return string The parsed layout string.
         */
        public function renderLayout(): string {
            return $this->parse($this->layout);
        }
        
        /**
         * Renders the view by parsing the file.
         *
         * @return string The parsed content of the file.
         */
        public function renderView(): string {
            return $this->parse($this->file);
        }
        
        /**
         * Renders the content of the view and returns it as a string.
         *
         * @return string The rendered content of the view.
         */
        public function render(): string {
            $content = $this->renderView();
            $this->with(['content' => $content]);
            if ($this->layout) {
                return $this->renderLayout();
            }
            
            return $content;
        }
        
        # --
        
        /**
         * Converts the view to HTML format and returns it as a string.
         *
         * @return string The view rendered as HTML.
         */
        public function toHtml(): string {
            return $this->render();
        }
        
        /**
         * Returns the string representation of the object.
         *
         * @return string The string representation of the object.
         */
        public function toString(): string {
            return $this->render();
        }
        
        
        /**
         * Returns a string representation of the object.
         *
         * @return string The string representation of the object.
         */
        public function __toString(): string {
            return $this->toString();
        }
        
    }
