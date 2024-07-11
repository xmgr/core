<?php
    
    namespace Xmgr\Security;
    
    /**
     * Class Totp
     *
     * This class represents a Time-based One-Time Password (TOTP) generator.
     * It provides methods for generating TOTP tokens, setting data, period, offset,
     * secret, algorithm, and verifying TOTP tokens.
     *
     * @package YourPackage
     */
    class Totp {
        
        public const string SECRET = 'JpYHjxzQaoTukjwswKHZjqXLcP3ZEGHS';
        
        public const int PERIOD_HOURLY = 3600;
        public const int PERIOD_5M     = 60;
        public const int PERIOD_15M    = 900;
        public const int PERIOD_30M    = 1800;
        public const int PERIOD_DAILY  = 86400;
        
        protected string $data      = '';
        protected string $secret    = self::SECRET;
        protected int    $offset    = 0;
        protected int    $period    = 3600;
        protected string $algo      = 'sha256';
        protected int    $maxlength = 0;
        
        /**
         * Class constructor.
         *
         * This constructor initializes a new instance of the class with the provided data, period, and offset.
         * It also calls the `setPeriod()`, `setData()`, and `setOffset()` methods to set the values of the object's
         * properties.
         *
         * @param string $data   The data to be used for generation (default: '').
         * @param int    $period The period for generation (default: self::PERIOD_HOURLY).
         * @param int    $offset The offset for generation (default: 0).
         */
        public function __construct(string $data = '', ?string $secret = null, int $period = self::PERIOD_HOURLY, int $offset = 0) {
            $this->setPeriod($period);
            $this->setData($data);
            $this->setOffset($offset);
            $this->setSecret($secret);
        }
        
        /**
         * Create a new instance of Totp.
         *
         * This method creates a new instance of the Totp class with the provided data, period, and offset.
         *
         * @param string $data   The data used for generating the Totp.
         * @param int    $period The time period in seconds for the Totp.
         * @param int    $offset The time offset in seconds for the Totp.
         *
         * @return Totp The newly created instance of Totp.
         */
        public static function create(string $data = '', ?string $secret = null, int $period = self::PERIOD_HOURLY, int $offset = 0): Totp {
            return new static($data, $secret, $period, $offset);
        }
        
        /**
         * Set the data for the object.
         *
         * This method sets the data for the object by updating the value of the $data property.
         *
         * @param string $data The data to be set for the object.
         *
         * @return self The updated instance of the object.
         */
        public function setData(string $data): self {
            $this->data = $data;
            
            return $this;
        }
        
        /**
         * Set the period.
         *
         * This method sets the period to be used for generating the output. If a period
         * is provided, it will be assigned to the `$period` property of the object.
         * If no period is provided (or if the parameter is null), the `$period` property
         * will not be modified.
         *
         * @param int|null $period The period to be set. If null, the period will not be modified.
         *
         * @return self The current instance of the object.
         */
        public function setPeriod(?int $period = null): self {
            if ($period) {
                $this->period = $period;
            }
            
            return $this;
        }
        
        /**
         * Set the offset value.
         *
         * This method sets the offset value for the current instance. The offset value
         * is used in the `generate` method to calculate the generated output. If no
         * offset value is provided, it is set to 0 by default.
         *
         * @param int $offset The offset value to set.
         *
         * @return self The updated instance of the current class.
         */
        public function setOffset(int $offset = 0): self {
            $this->offset = $offset;
            
            return $this;
        }
        
        /**
         * Set the secret.
         *
         * This method sets the secret used for generating the output.
         *
         * @param string|null $secret The secret to be set.
         *
         * @return self Returns the instance of the class after setting the secret.
         */
        public function setSecret(?string $secret): self {
            $this->secret = $secret ?? static::SECRET;
            
            return $this;
        }
        
        
        /**
         * Set the algorithm for generating the output.
         *
         * This method sets the algorithm to be used for generating the output. The provided
         * algorithm must be a valid hashing algorithm according to the `hash_algos` function.
         * If the provided algorithm is valid, it will be converted to lowercase and assigned
         * to the `algo` property of the object.
         *
         * @param string $algo The algorithm to be used for generating the output.
         *
         * @return self The current object instance.
         */
        public function setAlgo(string $algo): self {
            $algo = strtolower($algo);
            if (in_array($algo, hash_algos())) {
                $this->algo = $algo;
            }
            
            return $this;
        }
        
        /**
         * Generates a hash value based on the provided data.
         *
         * @param string      $data        The data to be hashed. Defaults to an empty string if not provided.
         * @param string|null $secret      TOTP Secret
         * @param int|null    $period      The period used for hashing. Defaults to null if not provided.
         * @param string      $algo        The algorithm used for hashing. Defaults to "sha256" if not provided.
         * @param int         $time_offset The offset value used for hashing. Defaults to 0 if not provided.
         *
         * @return string The hashed value of the provided data.
         */
        public static function generate(string $data = '', ?string $secret = null, ?int $period = null, string $algo = 'sha256', int $time_offset = 0): string {
            return hash($algo, (string)(int)((time() + $time_offset) / $period) . $secret . $data);
        }
        
        /**
         * Get the generated output.
         *
         * This method retrieves the generated output by calling the `generate` method
         * with the provided data, period, algorithm, and offset.
         *
         * @return string The generated output as a string.
         */
        public function get(): string {
            return self::generate($this->data, $this->secret, $this->period, $this->algo, $this->offset);
        }
        
        /**
         * Verifies if the provided TOTP token is valid.
         *
         * @param string      $totp The TOTP token to verify.
         * @param string|null $data The data used to generate the TOTP token. If not provided, the default data set in
         *                          the class will be used.
         *
         * @return bool Returns true if the provided TOTP token is valid, otherwise false.
         */
        public function verify(string $totp, ?string $data = null): bool {
            $data = $data ?? $this->data;
            foreach ([$this->offset - $this->period, 0, $this->offset + $this->period] as $offset) {
                $token = self::generate($data, $this->secret, $this->period, $this->algo, $offset);
                if ($this->maxlength) {
                    $totp  = mb_substr($totp, 0, $this->maxlength);
                    $token = mb_substr($token, 0, $this->maxlength);
                }
                if ($totp === $token) {
                    return true;
                }
            }
            
            return false;
        }
        
    }
