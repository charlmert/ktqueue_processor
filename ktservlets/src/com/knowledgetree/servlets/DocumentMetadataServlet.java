package com.knowledgetree.servlets;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;

import javax.activation.MimetypesFileTypeMap;
import javax.servlet.ServletException;
import javax.servlet.ServletOutputStream;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.fileupload.FileItem;
import org.apache.commons.fileupload.FileItemFactory;
import org.apache.commons.fileupload.disk.DiskFileItemFactory;
import org.apache.commons.fileupload.servlet.ServletFileUpload;

import com.knowledgetree.metadata.KTMetaData;

public class DocumentMetadataServlet extends HttpServlet {

	private static final long serialVersionUID = 2069882175819537711L;
    
	private static final int TYPE_DOCX = 1; 
	private static final int TYPE_XLSX = 2; 
	private static final int TYPE_PPTX = 3; 
	
    private String fileName;
    private String contentType;
    private Map<String, String> metadata;
    
    public void doPost(HttpServletRequest request,HttpServletResponse response) throws ServletException {
    	try {
			init(request);
			getFile(request);
			process();
			writeResultToOutputStream(response);
		} catch (Exception e) {
			throw new ServletException("Unable to insert metadata: " + e.getMessage());
		}
    }
    
    private void process() {
    	
        KTMetaData ktm = KTMetaData.get();
        int res  = ktm.writeMetadata(fileName, fileName, metadata);
        
        if (res != 0) {
        	res = ktm.writeOOXMLProperty(fileName, fileName, TYPE_DOCX, metadata);
            if (res != 0) {
            	res = ktm.writeOOXMLProperty(fileName, fileName, TYPE_XLSX, metadata);
                if (res != 0) {
                	res = ktm.writeOOXMLProperty(fileName, fileName, TYPE_PPTX, metadata);
                }
            }
        }
    }

    private void init(HttpServletRequest request) throws Exception {
    	metadata = getParameterMap(request);
    }

    private String getMimeType() {
    	
        String fileMime = new MimetypesFileTypeMap().getContentType(fileName);
        
        //Getting best fit Mime Type
        if (fileMime == null){
        	if (contentType != null) {
        		fileMime = contentType;
        	} else {
        		fileMime = "application/octet-stream";
        	}
        }

        return fileMime;
    }
    	
        
    private void writeResultToOutputStream(HttpServletResponse response) throws IOException {
        //Returning the file as a raw/binary output stream
		File file = new File(fileName);
        FileInputStream in  = new FileInputStream(file);
        ServletOutputStream out = response.getOutputStream();
        
        response.setContentType( getMimeType() );
        response.setContentLength( (int)file.length() );
        response.setHeader( "Content-Disposition", "attachment; filename=\"" + fileName + "\"" );
        
        try {
            byte[] buf = new byte[1024];
            int i = 0;
            while ((i = in.read(buf)) != -1) {
                out.write(buf, 0, i);
            }
            out.flush();
        }
        catch (Exception e) {
            throw new IOException();
        }
        finally {
            if (in != null) in.close();
            if (out != null) out.close();
        }
    
    }
        
    
    private Map<String, String> getParameterMap(HttpServletRequest request) throws Exception {
    	
    	Map<String, String> metadata = new HashMap<String, String>();
    	
    	for(Iterator i = request.getParameterMap().entrySet().iterator();i.hasNext();){
    		Map.Entry entry = (Map.Entry)i.next();
    		String key = entry.getKey().toString();
    		String[] values = (String[]) entry.getValue();
    		
    		metadata.put(key, values[0]);
    		if (values.length > 1) {
    			throw new Exception("Duplicate Metadata Key Exception: Metadata key was specified more than once for update (" + key + " as " + values[1]  + ")");
    		}
    		
    	}
    	
    	return metadata;
    }
    
    
    private File getFile(HttpServletRequest request) throws Exception {
    	
    	File file = null;
    	FileItemFactory factory = new DiskFileItemFactory();
    	ServletFileUpload upload = new ServletFileUpload(factory);
    	List items = upload.parseRequest(request);

       	Iterator iter = items.iterator();
       	while (iter.hasNext()) {
            FileItem item = (FileItem) iter.next();
            
            if (!item.isFormField()) {
    	        fileName = item.getName();
    	        file = new File(fileName);
    	        contentType = item.getContentType();
    	        item.write(file);
    	        break;
            }
       	}
       	
       	return file;
       	
    }	
    
}
