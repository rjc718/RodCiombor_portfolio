<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>Bootstrap</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script type="text/javascript" src="jquery-1.11.1.min.js"></script>
    
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  
    <link rel="stylesheet" href="template_design.css" type="text/css" />
    
    <script type="text/javascript">
        
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
        
    </script>
    
  
</head>

<body>

<div class="row">
    <div class="col-md-12" id="header">
        <h1 id="site-name">Header</h1>
    
    <form action="">
        <select id="selTheme" style="color: black; float: right; margin-right: 50px;">
            <option value="default">Choose a theme</option>
            <option value="theme1">Theme 1</option>
            <option value="theme2">Theme 2</option>
            <option value="theme3">Theme 3</option>
        </select>
    </form>
    
    </div>
</div>

<nav class="navbar navbar-default">
           <div class="container-fluid" id="main_nav">
              
              <button style="float: left;" type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mainNavBar">MENU</button>
              
                
              <div class="collapse navbar-collapse" id="mainNavBar">
                 <ul class="nav navbar-nav">
                    
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">TEMPLATE 1<span class="caret"></span></a>
                        
                                <ul class="dropdown-menu">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column</a>
                                            </li>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column-S</a>
                                            </li>
                                        </div>
                                    </div>
                                </ul>
                    </li>
                    
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">TEMPLATE 2<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column</a>
                                            </li>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column-S</a>
                                            </li>
                                        </div>
                                    </div>
                                </ul>
                    </li>
                    
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">TEMPLATE 3<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column</a>
                                            </li>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column-S</a>
                                            </li>
                                        </div>
                                    </div>
                                </ul>
                    </li>
                    
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">TEMPLATE 4<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column</a>
                                            </li>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column-S</a>
                                            </li>
                                        </div>
                                    </div>
                                </ul>
                    </li>
                    
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">TEMPLATE 5<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column</a>
                                            </li>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <li>
                                                <a href="#">One Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 75-25-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Two Column: 25-75-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Three Column-S</a>
                                            </li>
                                            <li>
                                                <a href="#">Four Column-S</a>
                                            </li>
                                        </div>
                                    </div>
                                </ul>
                    </li>
                    
                </ul>
                <!--
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#">LOG OUT</a>
                    </li>
                </ul> 
                 -->
            </div>
        </div>
    </nav>


<div class="container" id="wrapper">

        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-6 content-col">
                <h4>Left</h4>
                <p>
                    Vel delenit nostrum gloriatur cu. Ius graeci iisque evertitur ad.
                    Ius at dicant praesent reformidans. Quas repudiandae at usu. a duo aliquam utroque deleniti, duo graeco viderer quaestio cu. Id vim eruditi vivendo delicatissimi, sea delenit ancillae ei. Ea quas ullum instructior per, discere volutpat per ne.
                    Cu vix dolorum tibique, nec ut probo adipisci gloriatur. Ad duo quas atqui ludus, mei ut dissentiet accommodare.Mei decore splendide ut, vim saperet volumus ne.
                    Prompta ceteros consequat per te. An sit modus nonumes. Tantas tincidunt id vix.
                    No pro dolor denique, vix dicunt insolens voluptatum in.Nulla platonem pri ea, sea sanctus accommodare et. Usu utamur civibus at, te est odio nostrum, qui indoctum convenire ad. Mei eu dicunt iisque impedit, cum graeci moderatius no.
                    Ex malis populo suscipit est, te mei tamquam partiendo, senserit similique ad vix. Eruditi urbanitas eu mea, est no dicit nominati.
                    
                 
                </p>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-6 content-col">
                <h4>Middle</h4>
                <p>
                    Id his agam eruditi philosophia, facilisi perpetua te cum. Nec ea postea officiis.
                    Perfecto appellantur sea no, eu nam honestatis concludaturque.
                    Vis in interesset liberavisse, eam ad tota nobis dignissim, no vis cetero fierent sensibus.
                    Ius ei veniam nemore feugait. Euripidis aliquando mea te, quo id duis dolorum torquatos.
                    Vim in wisi ponderum apeirian, has et ipsum ornatus.   Nec agam blandit insolens ne, an augue regione omittantur sea, noluisse voluptaria mei ex.
                    Usu tation scribentur at, te per omnesque voluptaria percipitur, integre singulis adolescens has ad.
                    Qui et dolorum probatus, pro ne dicat causae minimum. Percipit percipitur mei in, ad solum omittam ponderum eam. Ex esse postea has, porro adipisci mea ex.
                    At vix minimum atomorum liberavisse, movet veritus ut sea.
                </p>
            </div>
            <div class="col-md-4 col-sm-12 col-xs-12 content-col">
                <h4>Right</h4>
                <p>
                    Ut qui cibo malorum, eu pro labore delicatissimi vituperatoribus.
                    Laudem partem duo an. Mel saepe verterem elaboraret id. Tamquam vivendum vis an. Splendide sadipscing dissentiunt eu sit. Vel ne impedit maiorum definitiones, liber paulo torquatos ea sed.
                    Ius choro ullamcorper ei, inciderint cotidieque per ne. Nam commune antiopam petentium ei, sed id natum dolor lobortis, vis id novum clita legimus. Mel saperet salutatus ad. Dolores periculis nam et. Mei eu aliquip blandit.
                    Ut habemus praesent mea. Audire mediocrem theophrastus his an, an idque legere vel. Cu vix minim moderatius, ea nam eirmod facilisi. No audiam epicurei per, convenire honestatis signiferumque in sed, voluptua argumentum sed an.
                    Ad eam idque ludus vidisse, ubique libris molestie ex vix.
                </p>
            </div>
        </div>

</div>

<div class="row">
    <div class="col-md-12" id="footer">
        <h4>Footer</h4>
        <div id="footer-text">
            <p>
            Vel delenit nostrum gloriatur cu. Ius graeci iisque evertitur ad.
            Ius at dicant praesent reformidans. Quas repudiandae at usu.
            </p>
            <p>
            Ea duo aliquam utroque deleniti, duo graeco viderer quaestio cu.
            Id vim eruditi vivendo delicatissimi, sea delenit ancillae ei.
            Ea quas ullum instructior per, discere volutpat per ne.
            Cu vix dolorum tibique, nec ut probo adipisci gloriatur.
            Ad duo quas atqui ludus, mei ut dissentiet accommodare.
            </p>
            <p>
            Mei decore splendide ut, vim saperet volumus ne.
            Prompta ceteros consequat per te. An sit modus nonumes.
            Tantas tincidunt id vix. No pro dolor denique, vix dicunt insolens voluptatum in.
            </p>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        //init()
        $('#selTheme').change(function(){
            var selVal = $('#selTheme').val();
            changeTheme(selVal);
            });
    });
</script>

</body>

</html>