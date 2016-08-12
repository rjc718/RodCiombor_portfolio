<!--Template of search results page...also initializes all javascript functions-->


<?php include 'header.php'; ?>

<?php include 'srp_content_list.php'; ?>

<!--Set up event handlers, display default sort-->   
    <script type="text/javascript">
        $(document).ready(function(){
            init();
        });
    </script>
<?php include 'footer.php'; ?>