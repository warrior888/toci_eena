<?php

    interface IParser {
        
        /**
        * @desc Transform the data into an system understood array
        */
        public function getDataList($receivedData);
    }