using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Drawing;

public partial class Lab02 : System.Web.UI.Page
{
    protected void chkBold_CheckedChanged(object sender, System.EventArgs e) {
        lblDisplayText.Font.Bold = chkBold.Checked;
    }

    protected void chkItalics_CheckedChanged(object sender, System.EventArgs e)
    {
        lblDisplayText.Font.Italic = chkItalics.Checked;
    }

    protected void chkVisible_CheckedChanged(object sender, System.EventArgs e)
    {
        lblDisplayText.Visible = chkVisible.Checked;
    }

    protected void txtUserEntry_TextChanged(object sender, System.EventArgs e)
    {
        lblDisplayText.Text = txtUserEntry.Text;
    }

    protected void ddlForeColor_SelectedIndexChanged(object sender, System.EventArgs e)
    {
        lblDisplayText.ForeColor = Color.FromName(ddlForeColor.SelectedValue);
    }

    protected void ddlBackColor_SelectedIndexChanged(object sender, System.EventArgs e)
    {
        lblDisplayText.BackColor = Color.FromName(ddlBackColor.SelectedValue);
    }

    protected void ddlFontSize_SelectedIndexChanged(object sender, System.EventArgs e)
    {
        lblDisplayText.Font.Size = FontUnit.Parse(ddlFontSize.SelectedValue);
    }



    protected void txtUserEntry_TextChanged1(object sender, EventArgs e)
    {

    }
    protected void ddlForeColor_SelectedIndexChanged1(object sender, EventArgs e)
    {

    }
}