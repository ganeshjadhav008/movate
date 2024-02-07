# Movate

Requirements:
1. Create a page where there are 2 tabs for ‘View Claims’ and ‘Submit Claims’.
2. Upon choosing Submit claims, a form to open for user to submit their claims with the
fields in Appendix 01. Choose the best data type for these fields. Follow the validation
rule as given.
3. Store all date in a local JSON file through an RestAPI
4. User should be able to submit the claims successfully. Insert the data as given in
Appendix 02.
5. Upon choosing the View claims, user should land on a screen where the claims be
filtered by the below parameters.
a. Patient name – Use Drop down
b. Claims number – Auto complete field.
c. Service Type
d. Start Date – Use calendar to choose the data. Also, user should be able to type
the date. User should be restricted to choose dates within the last 18 months.
e. End Date. This is start date and end date are used to filter by the submission
date.

6. When filter is applied, data should be retrieved through a RestAPI and displayed in a
table format.
7. Introduce pagination for every 5 records.
8. Export the result to a csv file/excel/notepad. Choose any one file format though excel is
preferred.


Step to config Module

1. Place Module inside module->custom dir
2. Enable Module via drush[drush pm:enable <module_name>] or manually [/admin/modules]
   <img width="1440" alt="image" src="https://github.com/ganeshjadhav008/movate/assets/41983092/d8b0d557-1ed0-4c67-b367-03203b2d6171">

3. Enable REST resource [admin/config/services/rest], Resource Name: Claims Resource.
  <img width="1382" alt="image" src="https://github.com/ganeshjadhav008/movate/assets/41983092/fa345ec7-bcb6-44d7-860c-3e2c84c204fb">

4. Make Sure added permission for Resource.
  ** Access GET on Claims Resource resource
   Access POST on Claims Resource resource**
<img width="1351" alt="image" src="https://github.com/ganeshjadhav008/movate/assets/41983092/62fe4d86-1e22-45a5-b7aa-2e56ecbfc581">

5.After Enable config we can able to save/view/export record
URL: /submit-claims
![image](https://github.com/ganeshjadhav008/movate/assets/41983092/0bdaafd5-066b-4b9c-8342-cbc643fab909)
URL: /view-claims
<img width="1440" alt="image" src="https://github.com/ganeshjadhav008/movate/assets/41983092/d00b9564-5dae-45c7-925e-6cc2cff772c6">

