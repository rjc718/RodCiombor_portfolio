<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Lab05.aspx.cs" Inherits="Project1" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>Rod Ciombor - Lab #5</title>
     <link rel="stylesheet" href="cis2350.css" type="text/css" media="all" />
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
</head>
<body>
    <form id="form1" runat="server">
    <div>
        <h1>Rod Ciombor - Lab #5</h1>
        <h2>Body Mass Index Calculator</h2>
        Enter your measurements:
        <br />

        <asp:MultiView ID="mainView" runat="server" ActiveViewIndex="0">
            <asp:View ID="impView" runat="server">
              <asp:TextBox ID="ftBox" runat="server" AutoPostBack="true"></asp:TextBox>
              <asp:Label ID="ftLabel" runat="server" Text="feet"></asp:Label>
                <asp:RangeValidator 
                    ID="rgvTextFeet"
                    runat="server"
                    Type="Double"
                    Display="Dynamic"
                    MinimumValue="3"
                    MaximumValue="8"
                    SetFocusOnError="true"
                    ErrorMessage="*Height must be between 3 feet and 8 feet"
                    ControlToValidate="ftBox">
                </asp:RangeValidator>
                <asp:RequiredFieldValidator 
                    ID="rqvTextFeet"
                    runat="server"
                    ErrorMessage="*This is a required field"
                    Display="Dynamic"
                    SetFocusOnError="true"
                    ControlToValidate="ftBox">
                </asp:RequiredFieldValidator>      
                <br />

             <asp:TextBox ID="inchBox" runat="server" AutoPostBack="true"></asp:TextBox>
             <asp:Label ID="inchLabel" runat="server" Text="inches"></asp:Label>
                <asp:RequiredFieldValidator 
                    ID="rqvTextInch" 
                    runat="server"
                    Display="Dynamic"
                    ControlToValidate="inchBox" 
                    SetFocusOnError="true"
                    ErrorMessage="*This is a required field">
                </asp:RequiredFieldValidator> 
                <asp:CompareValidator 
                    ID="cpvTextInches" 
                    runat="server" 
                    Display="Dynamic"
                    ControltoValidate="inchBox"
                    SetFocusOnError="true"
                    Operator="LessThan"
                    ValueToCompare="12"
                    Type="Double"
                    ErrorMessage="*Inches must be less than 12">
                </asp:CompareValidator>
                <br />

             <asp:TextBox ID="poundsBox" runat="server" AutoPostBack="true"></asp:TextBox>
             <asp:Label ID="poundsLabel" runat="server" Text="pounds"></asp:Label>
                <asp:RangeValidator 
                    ID="rgvTextPounds"
                    runat="server"
                    Type="Double"
                    Display="Dynamic"
                    MinimumValue="50"
                    MaximumValue="600"
                    ErrorMessage="*Weight must be between 50 and 600 pounds"
                    SetFocusOnError="true"
                    ControlToValidate="poundsBox">
                </asp:RangeValidator>
                <asp:RequiredFieldValidator 
                    ID="rqvTextPounds" 
                    runat="server"
                    Display="Dynamic"
                    ControlToValidate="poundsBox" 
                    SetFocusOnError="true"
                    ErrorMessage="*This is a required field">
                </asp:RequiredFieldValidator>
                <br />

          </asp:View>

          <asp:View ID="metView" runat="server">

             <asp:TextBox ID="cmBox" runat="server" AutoPostBack="true"></asp:TextBox>
             <asp:Label ID="cmLabel" runat="server" Text="centimeters"></asp:Label>
                 <asp:RangeValidator 
                    ID="rgvTextCent"
                    runat="server"
                    Type="Double"
                    Display="Dynamic"
                    MinimumValue="100"
                    MaximumValue="250"
                    SetFocusOnError="true"
                    ErrorMessage="*Height must be between 100 and 250 centimeters"
                    ControlToValidate="cmBox">
                </asp:RangeValidator>
              <asp:RequiredFieldValidator 
                  ID="rqvTextCent"
                  Display="Dynamic"
                  runat="server"
                  ControlToValidate="cmBox" 
                  SetFocusOnError="true"
                  ErrorMessage="*This is a required field">
              </asp:RequiredFieldValidator>

               <br />

            <asp:TextBox ID="kgBox" runat="server" AutoPostBack="true"></asp:TextBox>
            <asp:Label ID="kgLabel" runat="server" Text="kilograms"></asp:Label>
              <asp:RequiredFieldValidator 
                  ID="rqvTextKg" 
                  runat="server" 
                  Display="Dynamic"
                  ControlToValidate="kgBox"
                  SetFocusOnError="true"
                  ErrorMessage="*This is a required field">
              </asp:RequiredFieldValidator>
               <asp:RangeValidator 
                    ID="rgvTextKg"
                    runat="server"
                    Type="Double"
                    Display="Dynamic"
                    MinimumValue="25"
                    MaximumValue="275"
                   SetFocusOnError="true"
                    ErrorMessage="*Width must be between 25 and 275 kilograms"
                    ControlToValidate="kgBox">
                </asp:RangeValidator>
               <br />
          </asp:View>

        </asp:MultiView>

        <asp:RadioButton ID="imp_Btn" runat="server" GroupName="system" AutoPostBack="true" Checked="true" OnCheckedChanged="imp_Btn_CheckedChanged" />
        <asp:Label ID="impLbl" runat="server" Text="Imperial"></asp:Label>
        <br />
        <asp:RadioButton ID="met_Btn" runat="server" GroupName="system" AutoPostBack="true" OnCheckedChanged="met_Btn_CheckedChanged" />
        <asp:Label ID="metLbl" runat="server" Text="Metric"></asp:Label>
         <br />
         <br />
        <asp:Button ID="calc_Btn" runat="server" Text="Calculate" OnClick="calc_Btn_Click" />
        <br />
        <br />
        <asp:Label ID="resultsLbl" runat="server" Text=""></asp:Label>
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
