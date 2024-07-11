<?php
    
    namespace Xmgr\Interfaces;
    
    use Xmgr\Collections\Collection;
    
    /**
     * Interface Collectable
     *
     * This interface represents an object that can be collected.
     * Classes implementing this interface must define a "collect" method.
     */
    interface Collectable {
        
        /**
         * Collects data from a source.
         *
         * This method is responsible for collecting data from a source. The source can be any kind of data provider
         * such as a database, API, or file system. The collected data is then processed or manipulated further by
         * other methods or components in the system.
         *
         * @return Collection
         */
        public function collect(): Collection;
        
    }
