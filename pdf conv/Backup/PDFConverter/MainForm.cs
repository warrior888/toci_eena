namespace PDFConverter
{
    using org.pdfbox.pdmodel;
    using org.pdfbox.util;
    using PdfSharp.Pdf;
    using PdfSharp.Pdf.IO;
    using System;
    using System.Collections.Generic;
    using System.ComponentModel;
    using System.Drawing;
    using System.IO;
    using System.Text.RegularExpressions;
    using System.Windows.Forms;

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
        private List<string> m_SoffiList = new List<string>();
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
            this.btnReadFile = new Button();
            this.ofdPlik = new OpenFileDialog();
            this.btnTempDirectory = new Button();
            this.fbdTempDirectory = new FolderBrowserDialog();
            this.btnOK = new Button();
            this.btnCopy = new Button();
            this.fbdDestDirectory = new FolderBrowserDialog();
            this.lblOffice = new Label();
            this.txtOffice = new TextBox();
            base.SuspendLayout();
            this.btnReadFile.Location = new Point(12, 12);
            this.btnReadFile.Name = "btnReadFile";
            this.btnReadFile.Size = new Size(150, 0x17);
            this.btnReadFile.TabIndex = 0;
            this.btnReadFile.Text = "Wczytaj plik PDF";
            this.btnReadFile.UseVisualStyleBackColor = true;
            this.btnReadFile.Click += new EventHandler(this.btnReadFile_Click);
            this.ofdPlik.Filter = "pliki PDF|*.pdf";
            this.btnTempDirectory.Location = new Point(12, 0x29);
            this.btnTempDirectory.Name = "btnTempDirectory";
            this.btnTempDirectory.Size = new Size(150, 0x17);
            this.btnTempDirectory.TabIndex = 1;
            this.btnTempDirectory.Text = "Podaj katalog tymczasowy";
            this.btnTempDirectory.UseVisualStyleBackColor = true;
            this.btnTempDirectory.Click += new EventHandler(this.btnTempDirectory_Click);
            this.btnOK.Location = new Point(12, 0x6d);
            this.btnOK.Name = "btnOK";
            this.btnOK.Size = new Size(150, 0x17);
            this.btnOK.TabIndex = 2;
            this.btnOK.Text = "Generuj Jarografy";
            this.btnOK.UseVisualStyleBackColor = true;
            this.btnOK.Click += new EventHandler(this.btnOK_Click);
            this.btnCopy.Location = new Point(12, 0x8a);
            this.btnCopy.Name = "btnCopy";
            this.btnCopy.Size = new Size(150, 0x17);
            this.btnCopy.TabIndex = 3;
            this.btnCopy.Text = "Kopiuj na serwer";
            this.btnCopy.UseVisualStyleBackColor = true;
            this.btnCopy.Click += new EventHandler(this.btnCopy_Click);
            this.lblOffice.AutoSize = true;
            this.lblOffice.Location = new Point(12, 0x43);
            this.lblOffice.Name = "lblOffice";
            this.lblOffice.Size = new Size(0x45, 13);
            this.lblOffice.TabIndex = 4;
            this.lblOffice.Text = "Nazwa biura:";
            this.txtOffice.Location = new Point(12, 0x53);
            this.txtOffice.Name = "txtOffice";
            this.txtOffice.Size = new Size(150, 20);
            this.txtOffice.TabIndex = 5;
            base.AutoScaleDimensions = new SizeF(6f, 13f);
            base.AutoScaleMode = AutoScaleMode.Font;
            base.ClientSize = new Size(0x1af, 0xf4);
            base.Controls.Add(this.txtOffice);
            base.Controls.Add(this.lblOffice);
            base.Controls.Add(this.btnCopy);
            base.Controls.Add(this.btnOK);
            base.Controls.Add(this.btnTempDirectory);
            base.Controls.Add(this.btnReadFile);
            base.MaximizeBox = false;
            base.Name = "MainForm";
            this.Text = "PDFConverter";
            base.ResumeLayout(false);
            base.PerformLayout();
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
                    item = Path.GetFileNameWithoutExtension(path);
                }
                else if (!this.m_SoffiList.Contains(item))
                {
                    this.m_SoffiList.Add(item);
                }
                else
                {
                    MessageBox.Show("Zdublowany nr soffi:" + item);
                }
                File.Move(path, string.Format("{0}.pdf", item));
            }
        }
    }
}

