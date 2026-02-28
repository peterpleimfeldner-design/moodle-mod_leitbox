import zipfile
import os

zip_path = r"C:\Users\peter\Desktop\recall_v1.zip"
if os.path.exists(zip_path):
    with zipfile.ZipFile(zip_path, 'r') as zipf:
        print("Files in ZIP:")
        for name in zipf.namelist():
            print(name)
else:
    print("ZIP not found!")
