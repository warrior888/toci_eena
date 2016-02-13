namespace PDFConverter
{
    using PdfSharp.Pdf;
    using PdfSharp.Pdf.IO;
    using System;
    using System.Collections.Generic;
    using System.ComponentModel;
    using System.Drawing;
    using System.IO;
    using System.Text.RegularExpressions;
    using System.Windows.Forms;
    using org.apache.pdfbox.pdmodel;
    using org.apache.pdfbox.util;

    public class MainForm : Form
    {
        private Button btnCopy;
        private Button btnOK;
        private Button btnReadFile;
        private Button btnTempDirectory;
        private IContainer components;
        private FolderBrowserDialog fbdDestDirectory;
        private FolderBrowserDialog fbdTempDirectory;
        private Label lblOffice;
        private string m_OfficeDirectory;
        private string m_PDFFile;
        private Regex m_Regex = new Regex(@"[^\d]", RegexOptions.Compiled);
        private Dictionary<string, int> m_SoffiList = new Dictionary<string, int>();
        private string m_TempDirectory;
        private OpenFileDialog ofdPlik;
        private TextBox txtOffice;

        public MainForm()
        {
            this.InitializeComponent();
        }

        private void btnCopy_Click(object sender, EventArgs e)
        {
            try
            {
                if (this.fbdDestDirectory.ShowDialog() == DialogResult.OK)
                {
                    string path = this.fbdDestDirectory.SelectedPath + @"\" + this.m_OfficeDirectory;
                    if (!Directory.Exists(path))
                    {
                        Directory.CreateDirectory(path);
                    }
                    foreach (string str2 in Directory.GetFiles(Environment.CurrentDirectory, "*.pdf", SearchOption.AllDirectories))
                    {
                        File.Move(str2, path + @"\" + Path.GetFileName(str2));
                    }
                }
                MessageBox.Show("Operacja zakończyła się pomyślnie", "Informacja", MessageBoxButtons.OK, MessageBoxIcon.Asterisk);
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.Message, "Błąd", MessageBoxButtons.OK, MessageBoxIcon.Hand);
            }
        }

        private void btnOK_Click(object sender, EventArgs e)
        {
            try
            {
                this.m_OfficeDirectory = this.txtOffice.Text;
                if ((!string.IsNullOrEmpty(this.m_PDFFile) && !string.IsNullOrEmpty(this.m_TempDirectory)) && !string.IsNullOrEmpty(this.m_OfficeDirectory))
                {
                    this.CreateAndClearDirectory();
                    this.SplitAndRenamePDFDocument();
                    MessageBox.Show("Operacja zakończyła się pomyślnie", "Informacja", MessageBoxButtons.OK, MessageBoxIcon.Asterisk);
                }
                else
                {
                    MessageBox.Show("Podaj plik PDF, nazwę biura oraz katalog tymczasowy", "Błąd", MessageBoxButtons.OK, MessageBoxIcon.Hand);
                }
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.Message, "Błąd", MessageBoxButtons.OK, MessageBoxIcon.Hand);
            }
        }

        private void btnReadFile_Click(object sender, EventArgs e)
        {
            if (this.ofdPlik.ShowDialog() == DialogResult.OK)
            {
                this.m_PDFFile = this.ofdPlik.FileName;
            }
        }

        private void btnTempDirectory_Click(object sender, EventArgs e)
        {
            if (this.fbdTempDirectory.ShowDialog() == DialogResult.OK)
            {
                this.m_TempDirectory = this.fbdTempDirectory.SelectedPath;
            }
        }

        private void CreateAndClearDirectory()
        {
            Environment.CurrentDirectory = this.m_TempDirectory;
            if (!Directory.Exists(this.m_OfficeDirectory))
            {
                Directory.CreateDirectory(this.m_OfficeDirectory);
            }
            Environment.CurrentDirectory = this.m_OfficeDirectory;
            foreach (string str in Directory.GetFiles(Environment.CurrentDirectory, "*.pdf", SearchOption.AllDirectories))
            {
                File.Delete(str);
            }
        }

        protected override void Dispose(bool disposing)
        {
            if (disposing && (this.components != null))
            {
                this.components.Dispose();
            }
            base.Dispose(disposing);
        }

        private static string GetTextFromPDFFIle(string input)
        {
            PDDocument doc = PDDocument.load(input);
            PDFTextStripper stripper = new PDFTextStripper();
            return stripper.getText(doc);
        }

        private void InitializeComponent()
        {
            this.btnReadFile = new System.Windows.Forms.Button();
            this.ofdPlik = new System.Windows.Forms.OpenFileDialog();
            this.btnTempDirectory = new System.Windows.Forms.Button();
            this.fbdTempDirectory = new System.Windows.Forms.FolderBrowserDialog();
            this.btnOK = new System.Windows.Forms.Button();
            this.btnCopy = new System.Windows.Forms.Button();
            this.fbdDestDirectory = new System.Windows.Forms.FolderBrowserDialog();
            this.lblOffice = new System.Windows.Forms.Label();
            this.txtOffice = new System.Windows.Forms.TextBox();
            this.SuspendLayout();
            // 
            // btnReadFile
            // 
            this.btnReadFile.Location = new System.Drawing.Point(12, 12);
            this.btnReadFile.Name = "btnReadFile";
            this.btnReadFile.Size = new System.Drawing.Size(150, 23);
            this.btnReadFile.TabIndex = 0;
            this.btnReadFile.Text = "Wczytaj plik PDF";
            this.btnReadFile.UseVisualStyleBackColor = true;
            this.btnReadFile.Click += new System.EventHandler(this.btnReadFile_Click);
            // 
            // ofdPlik
            // 
            this.ofdPlik.Filter = "pliki PDF|*.pdf";
            // 
            // btnTempDirectory
            // 
            this.btnTempDirectory.Location = new System.Drawing.Point(12, 41);
            this.btnTempDirectory.Name = "btnTempDirectory";
            this.btnTempDirectory.Size = new System.Drawing.Size(150, 23);
            this.btnTempDirectory.TabIndex = 1;
            this.btnTempDirectory.Text = "Podaj katalog tymczasowy";
            this.btnTempDirectory.UseVisualStyleBackColor = true;
            this.btnTempDirectory.Click += new System.EventHandler(this.btnTempDirectory_Click);
            // 
            // fbdTempDirectory
            // 
            this.fbdTempDirectory.HelpRequest += new System.EventHandler(this.fbdTempDirectory_HelpRequest);
            // 
            // btnOK
            // 
            this.btnOK.Location = new System.Drawing.Point(12, 109);
            this.btnOK.Name = "btnOK";
            this.btnOK.Size = new System.Drawing.Size(150, 23);
            this.btnOK.TabIndex = 2;
            this.btnOK.Text = "Generuj Jarografy";
            this.btnOK.UseVisualStyleBackColor = true;
            this.btnOK.Click += new System.EventHandler(this.btnOK_Click);
            // 
            // btnCopy
            // 
            this.btnCopy.Location = new System.Drawing.Point(12, 138);
            this.btnCopy.Name = "btnCopy";
            this.btnCopy.Size = new System.Drawing.Size(150, 23);
            this.btnCopy.TabIndex = 3;
            this.btnCopy.Text = "Kopiuj na serwer";
            this.btnCopy.UseVisualStyleBackColor = true;
            this.btnCopy.Click += new System.EventHandler(this.btnCopy_Click);
            // 
            // lblOffice
            // 
            this.lblOffice.AutoSize = true;
            this.lblOffice.Location = new System.Drawing.Point(12, 67);
            this.lblOffice.Name = "lblOffice";
            this.lblOffice.Size = new System.Drawing.Size(69, 13);
            this.lblOffice.TabIndex = 4;
            this.lblOffice.Text = "Nazwa biura:";
            // 
            // txtOffice
            // 
            this.txtOffice.Location = new System.Drawing.Point(12, 83);
            this.txtOffice.Name = "txtOffice";
            this.txtOffice.Size = new System.Drawing.Size(150, 20);
            this.txtOffice.TabIndex = 5;
            // 
            // MainForm
            // 
            this.ClientSize = new System.Drawing.Size(431, 244);
            this.Controls.Add(this.txtOffice);
            this.Controls.Add(this.lblOffice);
            this.Controls.Add(this.btnCopy);
            this.Controls.Add(this.btnOK);
            this.Controls.Add(this.btnTempDirectory);
            this.Controls.Add(this.btnReadFile);
            this.MaximizeBox = false;
            this.Name = "MainForm";
            this.Text = "PDFConverter";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        private void SplitAndRenamePDFDocument()
        {
            PdfDocument document = PdfReader.Open(this.m_PDFFile, PdfDocumentOpenMode.Import);
            string fileNameWithoutExtension = Path.GetFileNameWithoutExtension(this.m_PDFFile);
            for (int i = 0; i < document.PageCount; i++)
            {
                PdfDocument document2 = new PdfDocument();
                document2.Version = document.Version;
                document2.Info.Title = string.Format("Strona {0} of {1}", i + 1, document.Info.Title);
                document2.Info.Creator = document.Info.Creator;
                string path = string.Format("{0} - Strona {1}.pdf", fileNameWithoutExtension, i + 1);
                document2.AddPage(document.Pages[i]);
                document2.Save(path);
                string textFromPDFFIle = GetTextFromPDFFIle(path);
                string item = this.m_Regex.Replace(textFromPDFFIle, "-");
                string[] strArray = item.Split(new char[] { '-' }, StringSplitOptions.RemoveEmptyEntries);
                for (int j = 0; j < strArray.Length; j++)
                {
                    if (strArray[j].Length == 9)
                    {
                        item = strArray[j];
                        break;
                    }
                }

                if (item.Length != 9)
                {
                    item = "no_soffi";
                        //Path.GetFileNameWithoutExtension(path);
                }

                if (!this.m_SoffiList.ContainsKey(item))
                {
                    this.m_SoffiList.Add(item, 0);
                }
                else
                {
                    this.m_SoffiList[item]++;
                    //MessageBox.Show("Zdublowany nr soffi:" + item);
                }
                //byk, || !containsKey
                if ( this.m_SoffiList[item] == 0)
                    File.Move(path, string.Format("{0}.pdf", item));
                else
                    File.Move(path, string.Format("{0}_{1}.pdf", item, this.m_SoffiList[item]));
                
            }
        }

        private void fbdTempDirectory_HelpRequest(object sender, EventArgs e)
        {

        }
    }
}

