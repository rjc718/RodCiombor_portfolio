<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Lab04.aspx.cs" Inherits="Lab04" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>Rod Ciombor - Lab #4</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="Stylesheet" type="text/css" href="cis2350.css" />
</head>
<body>
    
    <form id="form1" runat="server">
    <div>
    
        <h1>Rod Ciombor - Lab #4</h1>
        
        <asp:RadioButton ID="adRadio" GroupName ="radButtons" runat="server" Checked="true" OnCheckedChanged="adRadio_CheckedChanged" AutoPostBack="true" />
        <asp:Label ID="adLabel" runat="server" Text="Label">Ad Rotator</asp:Label>
        
        <asp:RadioButton ID="linkRadio" GroupName ="radButtons" runat="server" OnCheckedChanged="linkRadio_CheckedChanged" AutoPostBack="true" />
        <asp:Label ID="linkLabel" runat="server" Text="Label">Quick Links</asp:Label>
        
        <asp:RadioButton ID="calRadio" GroupName ="radButtons" runat="server" OnCheckedChanged="calRadio_CheckedChanged" AutoPostBack="true" />
        <asp:Label ID="calLabel" runat="server" Text="Label">Calendar</asp:Label>
        <br />
        <br />

        <asp:MultiView ID="mainView" runat="server" ActiveViewIndex="0">

        <asp:View ID="adView" runat="server">
            <asp:Button ID="ref_Btn" runat="server" Text="Refresh" />
            <br />
            <br />
            <asp:AdRotator ID="AdRotator1" runat="server" AdvertisementFile="Lab04AdvertsRodCiombor.xml" Width="500" />
        </asp:View>
        <asp:View ID="linkView" runat="server">
            <asp:XmlDataSource ID="xdsQuickLinks" runat="server" DataFile="Lab04QuicklinksRodCiombor.xml"></asp:XmlDataSource>
            <asp:DropDownList ID="ddlLinks" runat="server" 
                DataSourceID="xdsQuickLinks" DataTextField="text" DataValueField="value" style="vertical-align: middle;">
            </asp:DropDownList>
            <asp:Button runat="server" Text="Go" ID="go_Btn" OnClick="go_Btn_Click" />
        </asp:View>

        <asp:View ID="calView" runat="server">
            <asp:Calendar runat="server" FirstDayOfWeek="Monday" BackColor="White" ID="calMain" OnSelectionChanged="calMain_SelectionChanged"></asp:Calendar>
            <br />
            <br />
            <asp:HyperLink runat="server" NavigateURL = "Lab04b.aspx">See the selected date</asp:HyperLink>
        </asp:View>
        </asp:MultiView>
        <br />
        <br />
    </div>
    </form>
<a href="http://validator.w3.org/check?uri=referer">
<img style="border:0;width:88px;height:31px" src="http://www.w3.org/Icons/valid-xhtml10" 
alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
</a>
<a href="http://jigsaw.w3.org/css-validator/check/referer">
<img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="Valid CSS!" />
</a> 
</body>
</html>
