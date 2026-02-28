import zipfile
import os

def create_zip():
    folder = r"c:\smartcards"
    zip_path = r"C:\Users\peter\Desktop\recall_v1.zip"
    
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(folder):
            for file in files:
                if "node_modules" in root or ".git" in root or ".gemini" in root or file == "zip_plugin.py":
                    continue
                
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, folder)
                
                # Include the root directory "recall" directly inside the zip
                arcname = "recall/" + arcname.replace('\\', '/')
                zipf.write(file_path, arcname)

    print("Plugin zipped successfully to C:\\Users\\peter\\Desktop\\recall_v1.zip!")

if __name__ == "__main__":
    create_zip()
