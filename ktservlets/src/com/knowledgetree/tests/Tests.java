package com.knowledgetree.tests;

import java.io.*;
import java.util.HashMap;
import java.util.Map;

import javax.activation.MimetypesFileTypeMap;
import javax.servlet.*;
import javax.servlet.http.*;

import com.knowledgetree.metadata.*;
import org.apache.poi.poifs.filesystem.OfficeXmlFileException;

public class Tests {
	
	private static final int TYPE_DOCX = 1; 
	private static final int TYPE_XLSX = 2; 
	private static final int TYPE_PPTX = 3; 
	
	public static void main(final String[] args) {
		
		String fileName = "/var/www/qa_test_data/Office 2007/2007 DOCX.docx";
		
		if (args[0] != null) {
			fileName = args[0];
		}
		
		String targetFile = fileName;
		
		if (args[1] != null) {
			targetFile = args[1];
		}

		String fileMime = new MimetypesFileTypeMap().getContentType(targetFile);
		
		//If file mime type doesn't yield a usable extension use the fileName extension.
		if (fileMime.indexOf("application/octet-stream") == 0) {
			
		} 
		
		//Writing the metadata here:
        Map <String, String> metadata = new HashMap<String, String>();
        metadata.put("DOC_ID", "KT JAVA CLI - ID 001");
        KTMetaData ktm = new KTMetaData();//KTMetaData.get();
        
        // Create a POIFileSystem object from the input document
        try{
        	InputStream inStream = new FileInputStream(fileName);
        	OutputStream outStream = new FileOutputStream(targetFile);
        	
            int res = ktm.writeMetadata(inStream, outStream, metadata);
        	System.out.println("Write Metadata Result: [" + res + "]");
            
            if (res != 0) {
            	//Could be an OOXML DOCX document
            	res = ktm.writeOOXMLProperty(fileName, targetFile, TYPE_DOCX, metadata);
            	System.out.println("Wrote OOXML DOCX doc : res [" + res + "]");
                if (res != 0) {
                	//Could be an OOXML XLSX workbook document
                	res = ktm.writeOOXMLProperty(fileName, targetFile, TYPE_XLSX, metadata);
                	System.out.println("Wrote OOXML XLSX doc : res [" + res + "]");
                    if (res != 0) {
                    	//Could be an OOXML PPTX slides document
                    	res = ktm.writeOOXMLProperty(fileName, targetFile, TYPE_PPTX, metadata);
                    	System.out.println("Wrote OOXML PPTX doc : res [" + res + "]");
                    }
                }
            }
            
        } catch (FileNotFoundException ex) {
        	
        }

        
        
		System.out.println("Dun");
	}
}
