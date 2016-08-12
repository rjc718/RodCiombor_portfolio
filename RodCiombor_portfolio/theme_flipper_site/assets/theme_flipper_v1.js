$(document).ready(function(){
    console.log('jsworks');
    
    });
        
        var theme1 = {
            "body_bgColor":"#252525",
            "header_bgColor":"white",
            "header_height":"130px",
            "header_mTop":"-20px",
            "header_pTop":"20px",
            "siteName_color":"#774493",
            "siteName_align":"center",
            "siteName_textTrans":"uppercase",
            "siteName_font": "'Open Sans', Verdana, sans-serif",
            "siteName_fontWeight":"200",
            "siteName_fontSize":"2.2em",
            "nav_bgColor":"#774493",
            "nav_color":"white",
            "nav_textShadow":"1px 1px 1px #140c19",
            "nav_fontSize":"0.925em",
            "nav_font": "'Open Sans', Verdana, sans-serif",
            "nav_fontWeight":"200",
            "nav_border":"none",
            "nav_display":"inline-block",
            "nav_float":"none",
            "nav_textAlign":"center",
            "nav_dropdown_bgColor":"#774493",
            "nav_dropdown_color":"white",
            "nav_dropdown_font":"'Open Sans', Verdana, sans-serif",
            "nav_dropdown_fontSize":"12px",
            "nav_dropdown_textShadow":"1px 1px 1px #140c19",
            "wrapper_bgColor":"white",
            "wrapper_margin":"-20px",
            "wrapper_border":"none",
            "footer_bgColor":"#252525",
            "footer_border":"#none",
            "footer_height":"200px",
            "footer_color":"white",
            "footer_textAlign":"center",
            "footer_h4fontSize":"24px",
            "footer_footerText_fontSize":"12px",
            "footer_footerText_mLeft":"auto",
            "footer_footerText_mRight":"auto",
            "footer_footerText_width":"750px",
            "footer_footerText_textAlign":"left",
            "footer_footerText_pLeft":"10px",
            "footer_footerText_pRight":"10px",
            "content_h4color":"#774493",
            "content_font":"'Roboto'"
            };
        
        var theme2 = {"header_bgColor":"blue"};
        var theme3 = {"header_bgColor":"purple"};
        
        var themes = [];
        themes["theme1"] = theme1;
        themes["theme2"] = theme2;
        themes["theme3"] = theme3;
        
        function changeTheme(value) {
           
            setHeader(value);
            setNav(value);
            setBody(value);
            setWrap(value);
            setContent(value);
            setFooter(value);
        }
        
        function setHeader(value) {
            $('#site-name').css('color', themes[value].siteName_color);
            $('#site-name').css('text-align', themes[value].siteName_align);
            $('#site-name').css('text-transform', themes[value].siteName_textTrans);
            $('#site-name').css('font-family', themes[value].siteName_font);
            $('#site-name').css('font-weight', themes[value].siteName_fontWeight);
            $('#site-name').css('font-size', themes[value].siteName_fontSize);
            
            $('#header').css('background-color', themes[value].header_bgColor);
            $('#header').css('min-height', themes[value].header_height);
            $('#header').css('margin-top', themes[value].header_mTop);
            $('#header').css('padding-top', themes[value].header_pTop);
            
        }
        
        function setFooter(value) {
            $('#footer').css('background-color', themes[value].footer_bgColor);
            $('#footer').css('color', themes[value].footer_color);
            $('#footer').css('text-align', themes[value].footer_textAlign);
            $('#footer').css('border', themes[value].footer_border);
            $('#footer').css('min-height', themes[value].footer_height);
            $('#footer-text').css('max-width', themes[value].footer_footerText_width);
            $('#footer h4').css('font-size', themes[value].footer_h4fontSize);
            $('#footer-text').css('font-size', themes[value].footer_footerText_fontSize);
            $('#footer-text').css('margin-left', themes[value].footer_footerText_mLeft);
            $('#footer-text').css('margin-right', themes[value].footer_footerText_mRight);
            $('#footer-text').css('padding-left', themes[value].footer_footerText_pLeft);
            $('#footer-text').css('padding-right', themes[value].footer_footerText_pRight);
            $('#footer-text').css('text-align', themes[value].footer_footerText_textAlign);
        }
        
        function setBody(value) {
            $('body').css('background-color', themes[value].body_bgColor);
            //$('body').css('color', themes[value].content_font);
        }
        
        function setNav(value) {
            $('#main_nav').css('background-color', themes[value].nav_bgColor);
            $('.navbar-default .navbar-nav > li > a').css('color', themes[value].nav_color);
            $('.navbar-default .navbar-nav > li > a').css('text-shadow', themes[value].nav_textShadow);
            $('.navbar-default .navbar-nav > li > a').css('font-size', themes[value].nav_fontSize);
            $('.navbar-default .navbar-nav > li > a').css('font-family', themes[value].nav_font);
            $('.navbar-default .navbar-nav > li > a').css('font-size', themes[value].nav_fontWeight);
            $('.navbar-default').css('border', themes[value].nav_border);
            
            $('.navbar .navbar-nav').css('display', themes[value].nav_display);
            $('.navbar .navbar-nav').css('float', themes[value].nav_float);
            $('.navbar .navbar-collapse').css('text-align', themes[value].nav_textAlign);
            $('.dropdown-menu').css('background-color', themes[value].nav_dropdown_bgColor);
            $('.dropdown-menu a').css('color', themes[value].nav_dropdown_color);
            $('.dropdown-menu').css('font-family', themes[value].nav_dropdown_font);
            $('.dropdown-menu').css('font-size', themes[value].nav_dropdown_fontSize);
            $('.dropdown-menu').css('text-shadow', themes[value].nav_dropdown_textShadow);
        
        }
        
        function setWrap(value) {
            $('#wrapper').css('background-color', themes[value].wrapper_bgColor);
            $('#wrapper').css('border', themes[value].wrapper_border);
            $('#wrapper').css('margin-top', themes[value].wrapper_margin);
        }
        
        function setContent(value) {
            $('.content-col h4').css('color', themes[value].content_h4color);
            $('.content-col p').css('font-family', themes[value].content_font);
           // "Roboto",Verdana,sans-serif
        }
        
