using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using System.Windows.Forms;
using ikvm.extensions;
using org.apache.pdfbox.pdmodel;
using org.apache.pdfbox.util;
using PdfSharp.Pdf;
using PdfSharp.Pdf.IO;

namespace PDFConverterMulti
{
    public partial class MainForm2 : Form
    {
        private FolderBrowserDialog fbdSource = new FolderBrowserDialog();
        private FolderBrowserDialog fbdDestination = new FolderBrowserDialog();
        private string folderSourcePath;
        private string folderDestinationPath;

        private Regex regex = new Regex(@"BurgerServiceNummer\s+(\d+\.\d+\.\d+)");

        public MainForm2()
        {
            InitializeComponent();
        }

        private void button1_Click(object sender, EventArgs e)
        {
            if (fbdSource.ShowDialog() == DialogResult.OK)
            {
                folderSourcePath = fbdSource.SelectedPath;
            }
        }

        private void button2_Click(object sender, EventArgs e)
        {
            if (fbdDestination.ShowDialog() == DialogResult.OK)
            {
                folderDestinationPath = fbdDestination.SelectedPath;
            }
        }

        private void button3_Click(object sender, EventArgs e)
        {
            if (string.IsNullOrWhiteSpace(folderSourcePath) || string.IsNullOrWhiteSpace(folderDestinationPath))
            {
                MessageBox.Show("Wskaż najpierw foldery", "Error", MessageBoxButtons.OK,
                    MessageBoxIcon.Error);
                return;
            }
            CreateAndClearDirectory(folderDestinationPath);
            GetPdfFilesFromDirectory(folderSourcePath).ToList().ForEach(RenamePdfDocument);
            MessageBox.Show("Operacja zakończyła się pomyślnie", "Informacja", MessageBoxButtons.OK, MessageBoxIcon.Asterisk);
        }

        private IEnumerable<string> GetPdfFilesFromDirectory(string path)
        {
            var pdfFilesPath = Directory.GetFiles(path, "*.pdf", SearchOption.TopDirectoryOnly);
            return pdfFilesPath;
        }

        private string GetTextFromPdfFIle(string input)
        {
            var doc = PDDocument.load(input);
            var stripper = new PDFTextStripper();
            return stripper.getText(doc);
        }

        private void CreateAndClearDirectory(string path)
        {
            if (!Directory.Exists(path))
            {
                Directory.CreateDirectory(path);
            }
            
            foreach (var str in Directory.GetFiles(path, "*.pdf", SearchOption.TopDirectoryOnly))
            {
                File.Delete(str);
            }
        }
        
        private void RenamePdfDocument(string path)
        {
            var textFromPdfFIle = GetTextFromPdfFIle(path);
            var match = regex.Match(textFromPdfFIle);

            if (match.Groups.Count == 2)
            {
                var soffi = match.Groups[1].Value.Replace(".", "");
                Environment.CurrentDirectory = folderDestinationPath;
                File.Move(path, string.Format("{0}.pdf", soffi));
            }
            else
            {
                MessageBox.Show("Nie potrafię wskazać soffnumeru w pliku: " + path, "Error", MessageBoxButtons.OK,
                    MessageBoxIcon.Error);
            }
        }
    }
}
