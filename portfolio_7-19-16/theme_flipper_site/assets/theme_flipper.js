        var t = [];
        t["theme1"] = theme1;
        t["theme2"] = theme2;

        
        function changeTheme(v) {
           
            setHeader(v);
            setNav(v);
            setBody(v);
            setWrap(v);
            setContent(v);
            setFooter(v);
        }
        
        function setHeader(v) {
              
            $('#header').css('background-color', t[v].header_bgColor);
            $('#site-name').css('color', t[v].header_color);
            $('#site-name').css('font-family', t[v].siteName_font);
            $('#site-name').css('font-size', t[v].siteName_fontSize);
            
        
        }
        
        function setFooter(v) {
            $('#footer').css('color', t[v].footer_color);
            $('#footer').css('background-color', t[v].footer_bgColor);
            $('#footer').css('font-family', t[v].footer_font);
            $('#footer h4').css('font-size', t[v].footer_h4fontSize);
            $('#footer-text').css('font-size', t[v].footer_footerText_fontSize);
            
            
        }
        function setBody(v) {
            $('#template1').css('background-color', t[v].body_bgColor_t1);
            $('#template2').css('background-color', t[v].body_bgColor_t2);
            $('#template3').css('background-color', t[v].body_bgColor_t3);
            $('#template4').css('background-color', t[v].body_bgColor_t4);
            $('#template5').css('background-color', t[v].body_bgColor_t5);
            
        }
        
        
        function windowSize(v) {           
            
            var view = $(window).width();
                if (view < 751) {
                    $('#mainNavBar').css('background-color', t[v].menuItem_bgColor_mob);
                    $('.navbar-inverse').css('background-color', t[v].nav_bgColor_mob);
                    $('.navbar-toggle').css('background-color', t[v].navBtn_bgColor);
                    
                    
                }
                else{
                    
                    $('#mainNavBar').css('background-color', t[v].menuItem_bgColor_full);
                    $('.navbar-inverse').css('background-color', t[v].nav_bgColor_full);
                    
                } 
        }
        
        function setNav(v) {
            
            $('.navbar-inverse .navbar-nav > li > a').css('font-size', t[v].nav_fontSize);
            $('.navbar-inverse .navbar-nav > li > a').css('font-family', t[v].nav_font);
            $('.dropdown-menu').css('font-family', t[v].nav_dropdown_font);
            $('.dropdown-menu').css('font-size', t[v].nav_dropdown_fontSize);
            $('.dropdown-menu > li > a').css('font-weight', t[v].nav_fontWeight);
            
            windowSize(v);
        
            $(window).resize(function() {
            
                windowSize(v);
                
            });    
        }
        
        function setWrap(v) {
            $('#wrapper').css('background-color', t[v].wrapper_bgColor);
        }
        
        function setContent(v) {
            $('.content-col h4').css('color', t[v].content_h4color);
            $('.content-col p').css('font-family', t[v].content_font);
           // "Roboto",Verdana,sans-serif
        }
        
