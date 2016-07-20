using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

public partial class Lab04 : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        if (!Page.IsPostBack)
        {
            calMain.SelectedDates.Add(Convert.ToDateTime((string)Session["RodCSelectedDate"]));
        }
    }
    protected void adRadio_CheckedChanged(object sender, EventArgs e)
    {
        mainView.SetActiveView(adView);
    }
    protected void linkRadio_CheckedChanged(object sender, EventArgs e)
    {
        mainView.SetActiveView(linkView);
    }
    protected void calRadio_CheckedChanged(object sender, EventArgs e)
    {
        mainView.SetActiveView(calView);
    }

    protected void calMain_SelectionChanged(object sender, EventArgs e)
    {
        Session["RodCSelectedDate"] = calMain.SelectedDate.ToString();
    }
    protected void go_Btn_Click(object sender, EventArgs e)
    {
            if (ddlLinks.SelectedValue != "")
                Response.Redirect(ddlLinks.SelectedValue);
    }
}