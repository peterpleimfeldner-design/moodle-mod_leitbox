import zipfile
import os

def create_zip():
    folder = r"c:\smartcards"
    zip_path = r"C:\Users\peter\Desktop\smartcards.zip"
    
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(folder):
            # Exclude node_modules, .git, and any temporary packaging scripts
            if 'node_modules' in root.split(os.sep):
                continue
            if '.git' in root.split(os.sep):
                continue
            
            for file in files:
                if file == "zip_plugin.py": 
                    continue # exclude the zipping script itself
                    
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, start=folder)
                # Include the root directory "smartcards" directly inside the zip
                arcname = "smartcards/" + arcname.replace('\\', '/')
                zipf.write(file_path, arcname)

create_zip()
print("Plugin zipped successfully to C:\\Users\\peter\\Desktop\\smartcards.zip!")
