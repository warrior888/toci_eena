<?php

    interface IOutput {
        
        /**
        * @desc Render output in any custom defined form
        */
        function RenderOutput($data, $format, $isAuthorized = false);
        
        function ExitError($errorCode);
    }