<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Lab02.aspx.cs" Inherits="Lab02" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head id="Head1" runat="server">
    <title>Rod Ciombor - Lab #2 - Update Label Properties</title>
    <link rel="stylesheet" href="cis2350.css" type="text/css" media="all" />
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />


</head>
<body>
    
    <form id="form1" runat="server">
    <div>
        <h1>Rod Ciombor - Lab #2 - Update Label Properties</h1>
        <br />
        <asp:Label ID="lblDisplayText" runat="server" />
        <br />
        <br />
        <br />
        Enter Text:
        <asp:TextBox ID="txtUserEntry" runat="server" AutoPostBack ="true" OnTextChanged="txtUserEntry_TextChanged" />
        <asp:Button ID="btnUpdate" runat="server" Text="Update Text" />
        <br />
        <br />
        Foreground Color:
        <asp:DropDownList ID="ddlForeColor" runat="server" AutoPostBack="true" OnSelectedIndexChanged="ddlForeColor_SelectedIndexChanged">
            <asp:ListItem Value="Black" Selected="True">Black</asp:ListItem>
            <asp:ListItem Value="White">White</asp:ListItem>
            <asp:ListItem Value="Red">Red</asp:ListItem>
            <asp:ListItem Value="Blue">Blue</asp:ListItem>
            <asp:ListItem Value="Green">Green</asp:ListItem>
            <asp:ListItem Value="Yellow">Yellow</asp:ListItem>
            <asp:ListItem Value="Purple">Purple</asp:ListItem>
        </asp:DropDownList>
        <br />
        Background Color:
        <asp:DropDownList ID="ddlBackColor" runat="server" AutoPostBack="true" OnSelectedIndexChanged="ddlBackColor_SelectedIndexChanged">
           <asp:ListItem Value="Black">Black</asp:ListItem>
            <asp:ListItem Value="White" Selected="True">White</asp:ListItem>
            <asp:ListItem Value="Red">Red</asp:ListItem>
            <asp:ListItem Value="Blue">Blue</asp:ListItem>
            <asp:ListItem Value="Green">Green</asp:ListItem>
            <asp:ListItem Value="Yellow">Yellow</asp:ListItem>
            <asp:ListItem Value="Purple">Purple</asp:ListItem>
        </asp:DropDownList>
        <br />
        Font Size:
        <asp:DropDownList ID="ddlFontSize" runat="server" AutoPostBack="true" OnSelectedIndexChanged="ddlFontSize_SelectedIndexChanged" CssClass="right">
            <asp:ListItem Value="X-Small" Text="X-Small"></asp:ListItem>
            <asp:ListItem Value="Small" Text="Small"></asp:ListItem>
            <asp:ListItem Value="Medium" Text="Medium" Selected="True"></asp:ListItem>
            <asp:ListItem Value="Large" Text="Large"></asp:ListItem>
            <asp:ListItem Value="X-Large" Text="X-Large"></asp:ListItem>
        </asp:DropDownList>
        <br />
        <br />
        <asp:CheckBox ID="chkBold" Text="Bold" runat="server" AutoPostBack="true" OnCheckedChanged="chkBold_CheckedChanged" />
        <br />
        <asp:CheckBox ID="chkItalics" Text="Italics" runat="server" AutoPostBack="true" OnCheckedChanged="chkItalics_CheckedChanged" />
        <br />
        <asp:CheckBox ID="chkVisible" Text="Visible" runat="server" AutoPostBack="true" Checked="true" OnCheckedChanged="chkVisible_CheckedChanged" />
        <br />
        <br />
        <br />
        <a href="http://validator.w3.org/check?uri=referer">
        <img style="border:0;width:88px;height:31px" src="http://www.w3.org/Icons/valid-xhtml10" 
        alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
        </a>
        <a href="http://jigsaw.w3.org/css-validator/check/referer">
        <img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="Valid CSS!" />
        </a> 

    </div>
    </form>
</body>
</html>
