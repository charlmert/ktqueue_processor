package com.knowledgetree.servlets;

import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;

import javax.activation.MimetypesFileTypeMap;

import com.knowledgetree.metadata.*;

import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.HashMap;

import org.apache.commons.fileupload.FileItem;
import org.apache.commons.fileupload.FileItemFactory;
import org.apache.commons.fileupload.disk.DiskFileItemFactory;
import org.apache.commons.fileupload.FileUploadException;
import org.apache.commons.fileupload.servlet.ServletFileUpload;

public class DocumentMetadataServlet extends HttpServlet {
    private static final long serialVersionUID = 2069882175819537711L;
    
	private static final int TYPE_DOCX = 1; 
	private static final int TYPE_XLSX = 2; 
	private static final int TYPE_PPTX = 3; 
	
    public void doPost(HttpServletRequest request,
    		HttpServletResponse response)
	        throws ServletException, IOException
    {

	    String sDocId = request.getParameter("literal.doc_id");
	    String sMetadataDocId = request.getParameter("metadata.doc_id");
	    
	    if (sDocId == null && sMetadataDocId == null) {
	    	response.setContentType("text/html");
		    PrintWriter out = response.getWriter();
		    out.println("<title>KnowledgeTree Metadata Error : </title> <body bgcolor=FFFFFF>");
		    out.println("Invalid Parameters: You need to provide the literal.doc_id as well as the metadata.doc_id you'd like to insert");
		    out.println("</body>");
		    out.close();
		    System.exit(1);
	    }
	    
	    if (sDocId == null && sMetadataDocId != null) {
	    	response.setContentType("text/html");
		    PrintWriter out = response.getWriter();
		    out.println("<title>KnowledgeTree Metadata Error : </title> <body bgcolor=FFFFFF>");
		    out.println("Invalid Parameters: You need to provide the S3/KT document id of the document your working on");
		    out.println("</body>");
		    out.close();
		    System.exit(1);
	    }
	    	
	    if (sDocId != null && sMetadataDocId == null) {
	    	response.setContentType("text/html");
		    PrintWriter out = response.getWriter();
		    out.println("<title>KnowledgeTree Metadata Error : </title> <body bgcolor=FFFFFF>");
		    out.println("Invalid Parameters: You need to provide the metadata.doc_id you'd like to insert");
		    out.println("</body>");
		    out.close();	    	
		    System.exit(1);
	    }
	    
		// Create a factory for disk-based file items
    	FileItemFactory factory = new DiskFileItemFactory();
    	// Create a new file upload handler
    	ServletFileUpload upload = new ServletFileUpload(factory);

    	List items;
        try {
        	// Parse the request
        	items = upload.parseRequest(request);

    		// Process the uploaded items
        	Iterator iter = items.iterator();
        	while (iter.hasNext()) {
        	    FileItem item = (FileItem) iter.next();

        	    if (!item.isFormField()) {
        	    	
	    	        String fieldName = item.getFieldName();
	    	        String fileName = item.getName();
	    	        String contentType = item.getContentType();
	    	        boolean isInMemory = item.isInMemory();
	    	        long sizeInBytes = item.getSize();
    	        
    	        	String targetFile = "tmp_" + fileName;
    	            File uploadedFile = new File(fileName);
    	            
    	            try {
    	            	//Writing file to temp
    	            	item.write(uploadedFile);
        	            
    	            	try{
    	            		
            	            //Writing the metadata to the uploaded file
            	            Map <String, String> metadata = new HashMap<String, String>();
            	            metadata.put("DOC_ID", sMetadataDocId);
            	            KTMetaData ktm = KTMetaData.get();
            	            int res  = ktm.writeMetadata(fileName, targetFile, metadata);
            	            
            	            //Handeling exception
            	            if (res != 0) {
            	            	//Could be an OOXML DOCX document
            	            	res = ktm.writeOOXMLProperty(fileName, targetFile, TYPE_DOCX, metadata);
            	            	//TODO: log info here
            	            	//System.out.println("Wrote OOXML DOCX doc : res [" + res + "]");
            	                if (res != 0) {
            	                	//Could be an OOXML XLSX workbook document
            	                	res = ktm.writeOOXMLProperty(fileName, targetFile, TYPE_XLSX, metadata);
                	            	//TODO: log info here
            	                	//System.out.println("Wrote OOXML XLSX doc : res [" + res + "]");
            	                    if (res != 0) {
            	                    	//Could be an OOXML PPTX slides document
            	                    	res = ktm.writeOOXMLProperty(fileName, targetFile, TYPE_PPTX, metadata);
            	                    	//TODO: log info here
            	                    	//System.out.println("Wrote OOXML PPTX doc : res [" + res + "]");
            	                    }
            	                }
            	            }
            	            
            	            //Returning the file as a raw/binary output stream
            	            String fileMime = new MimetypesFileTypeMap().getContentType(targetFile);

            	            File in = new File(targetFile);
            	            ServletOutputStream fos = response.getOutputStream();
            	            File retFile = new File(targetFile);
            	            
            	            //Getting best fit Mime Type
            	            if (fileMime == null){
            	            	if (contentType != null) {
            	            		fileMime = contentType;
            	            	} else {
            	            		fileMime = "application/octet-stream";
            	            	}
            	            }
            	            
            	            response.setContentType( fileMime );
            	            response.setContentLength( (int)retFile.length() );
            	            response.setHeader( "Content-Disposition", "attachment; filename=\"" + fileName + "\"" );
            	            
            	            FileInputStream fis  = new FileInputStream(in);
            	            try {
            	                byte[] buf = new byte[1024];
            	                int i = 0;
            	                while ((i = fis.read(buf)) != -1) {
            	                    fos.write(buf, 0, i);
            	                }
            	                fos.flush();
            	            }
            	            catch (Exception e) {
            	                throw e;
            	            }
            	            finally {
            	                if (fis != null) fis.close();
            	                if (fos != null) fos.close();
            	            } 
       	            	}
    	            	catch (Exception ex) {
        	            	throw new ServletException("Metadata Operation Failed : Couldnt read the ammended file.", ex);
    	            	}

    	            	
    	            } catch (Exception ex) {
    	            	throw new ServletException("Metadata Operation Failed : Couldnt write uploaded file to disk.", ex);
    	            }
        	    }
        	    
        	}
        } catch (FileUploadException ex) {
            throw new ServletException("Metadata Operation Failed : Couldnt upload file.", ex);
        }
    	
    	/*
        try {
        	KTMetaData docMeta = KTMetaData.get();
        	
        } catch (Exception exception) {
            throw new ServletException("Metadata Operation Failed", exception);
        }
    	*/
        
  }
    
    
    public void doGet(HttpServletRequest request, 
    		HttpServletResponse response)
	        throws ServletException, IOException
    {
	    response.setContentType("text/html");
	    PrintWriter out = response.getWriter();
	    
	    out.println("<title>KnowledgeTree Servlets GET</title> <body bgcolor=FFFFFF>");
		
	    out.println("<h2>GET Called</h2>");
	
	    String DATA = request.getParameter("DATA");
	
		if(DATA != null){
		  out.println(DATA);
		} else {
		  out.println("No text entered.");
		}
	    out.close();
    }
}
