using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

public partial class Project1 : System.Web.UI.Page
{


    protected void Page_Load(object sender, EventArgs e)
    {

    }
    protected void imp_Btn_CheckedChanged(object sender, EventArgs e)
    {
            mainView.SetActiveView(impView);
            kgBox.Text = "";
            cmBox.Text = "";
            resultsLbl.Text = "";
    }
    protected void met_Btn_CheckedChanged(object sender, EventArgs e)
    {
        mainView.SetActiveView(metView);
        ftBox.Text = "";
        inchBox.Text = "";
        poundsBox.Text = "";
        resultsLbl.Text = "";
       
    }
    protected void calc_Btn_Click(object sender, EventArgs e)
    {
        if (!Page.IsValid)
            return;
        
            double bmi;
            string condition = null;
            if (imp_Btn.Checked)
            {

                //Calculate imperial BMI
                double feet = Convert.ToDouble(ftBox.Text);
                double inches = Convert.ToDouble(inchBox.Text);
                double weight = Convert.ToDouble(poundsBox.Text);
                double height = (feet * 12.0) + inches;
                bmi = (weight * 703.0) / (height * height);

            }
            else
            {
                //Calculate metric BMI
                double cm = Convert.ToDouble(cmBox.Text);
                double kg = Convert.ToDouble(kgBox.Text);
                double height = cm / 100.0;
                double hgtSq = Math.Pow(height, 2.0);
                bmi = (kg / hgtSq);
            }

            //Determine physical condition, set text color
            if (bmi > 30.0)
            {
                condition = "obese";
                resultsLbl.CssClass = "red_text";
            }
            else if (bmi <= 30.0 && bmi > 25.0)
            {
                condition = "overweight";
                resultsLbl.CssClass = "orange_text";
            }
            else if (bmi <= 25.0 && bmi > 18.5)
            {
                condition = "normal";
                resultsLbl.CssClass = "green_text";
            }
            else
            {
                condition = "underweight";
                resultsLbl.CssClass = "blue_text";
            }

            resultsLbl.Text = "Your BMI is " + Math.Round(bmi, 2) + " which is in the " + condition + " range.";

        }
}