using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

public partial class Lab04b : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        if (String.IsNullOrEmpty((string)Session["RodCSelectedDate"]))
            lblDisplayText.Text = "You did not select a date.";
        else
            lblDisplayText.Text = "The date you selected is " + Session["RodCSelectedDate"];
    }
}