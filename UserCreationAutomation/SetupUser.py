from selenium import webdriver
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
import subprocess
import random
import time
import os

startTime = time.time()
preUsername = "User"
list_of_users = []

driver = webdriver.Chrome(ChromeDriverManager().install())
driver.get("https://datingapp.wearetheducklings.com/")
time.sleep(5)

numUsersToAdd = input("Please enter number of users: ")

for i in range(int(numUsersToAdd)):

    randNum = random.randint(0,100000000000)
    username = preUsername + str(randNum)
    randGender = random.randint(1,3)
    randSeeking = random.randint(1,3)

    try:
        driver.get("https://datingapp.wearetheducklings.com/registration/")
        driver.find_element_by_xpath('//*[@id="input_1_1_3"]').send_keys("Firstname")
        driver.find_element_by_xpath('//*[@id="input_1_1_6"]').send_keys("Lastname")
        driver.find_element_by_xpath('//*[@id="input_1_8"]').send_keys(username)
        driver.find_element_by_xpath('//*[@id="input_1_2"]').send_keys(username + "@email.com")
        driver.find_element_by_xpath('//*[@id="input_1_12"]').send_keys("1234")
        driver.find_element_by_xpath('//*[@id="input_1_12_2"]').send_keys("1234")
        driver.find_element(By.CSS_SELECTOR, "#input_1_5_chosen span").click()
        driver.find_element(By.CSS_SELECTOR, ".active-result:nth-child("+ str(randGender) + ")").click()
        driver.find_element(By.CSS_SELECTOR, "#input_1_11_chosen span").click()
        driver.find_element(By.CSS_SELECTOR, "#input_1_11_chosen .active-result:nth-child("+ str(randSeeking) + ")").click()
        driver.find_element(By.ID, "input_1_6").click()
        driver.find_element(By.LINK_TEXT, "7").click()
        driver.find_element(By.ID, "input_1_3").click()
        driver.find_element(By.ID, "input_1_3").send_keys("this is a short description")
        driver.find_element(By.ID, "choice_1_10_1").click()
        driver.find_element(By.ID, "gform_submit_button_1").click()
        list_of_users.append(username)
        print("User " + str(username) + " Registered")
    except:
        print("ERROR Creating user")
        continue

print(list_of_users)
driver.get("https://datingapp.wearetheducklings.com/wp-admin/")
driver.find_element_by_xpath('//*[@id="user_login"]').send_keys("khair_Admin")
driver.find_element_by_xpath('//*[@id="user_pass"]').send_keys("DonkeySquad!")
driver.find_element_by_name('wp-submit').click()

driver.get("https://datingapp.wearetheducklings.com/wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=gravityformsuserregistration_pending_activations&id=1")
while driver.execute_script("return (true)"):
    try:
        Select(driver.find_element(By.ID, "bulk-action-selector-top")).select_by_index(1)
        driver.find_element(By.ID, "cb-select-all-1").click()
        driver.find_element(By.ID, "doaction").click()
        time.sleep(5)
    except:
        break

driver.get("https://datingapp.wearetheducklings.com/wp-login.php?action=logout&_wpnonce=30dabeb2c7")
driver.find_element_by_xpath('//*[@id="error-page"]/div/p[2]/a').click()

driver.get("https://datingapp.wearetheducklings.com/log-in/")

userpassword = "1234"
numUsers = 0
for username in list_of_users:
    # Get to page
    try:
        driver.find_element_by_id("aam-login-username-widget-aam_backend_login-2-loginform").send_keys(username)
        driver.find_element_by_id("aam-login-password-widget-aam_backend_login-2-loginform").send_keys(userpassword)
        driver.find_element_by_id("aam-login-submit-widget-aam_backend_login-2-loginform").click()
        time.sleep(2)
    except:
        try:
            driver.find_element_by_id('wp-submit').click()
            continue
        except:
            driver.get("https://datingapp.wearetheducklings.com/log-in/")

    try:
        driver.get("https://datingapp.wearetheducklings.com/members/edit/")
    except:
        print("ERROR setting up user: " + username)
        continue
    
    # Random numbers
    randMonth = random.randint(0,11)
    randDay = random.randint(0,27)
    randYear = random.randint(1950,2002)
    randNumColour = random.randint(2,5)
    randRelationship = random.randint(1,8)
    randNumSeeking = random.randint(1,8)
    randomPhoto = random.randint(0,9)


    try:
        gender = Select(driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[1]/div[1]/div[3]/ul[1]/li[1]/div[1]/select[1]')).first_selected_option.text
    except:
        driver.get("https://datingapp.wearetheducklings.com/members/edit/")
        gender = Select(driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[1]/div[1]/div[3]/ul[1]/li[1]/div[1]/select[1]')).first_selected_option.text
    
    # Date
    ##Month
    Select(driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[1]/div[1]/div[3]/ul[1]/li[4]/div[1]/select[1]')).select_by_index(randMonth)

    ## Day
    Select(driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[1]/div[1]/div[3]/ul[1]/li[4]/div[2]/select[1]')).select_by_index(randDay)

    ## Year
    Select(driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[1]/div[1]/div[3]/ul[1]/li[4]/div[3]/select[1]')).select_by_value(str(randYear))

    # Colour
    success = False
    while success == False:
        for i in range(randNumColour):
            randColour = random.randint(1,5)
            try:
                driver.find_element_by_xpath('//*[@id="q_opt_ids17_chosen"]/ul[1]').click()
                driver.find_element_by_xpath('//*[@id="q_opt_ids17_chosen"]/div[1]/ul[1]/li['+ str(randColour) + ']').click()
            except:
                continue
            success = True

    # Current Relationship
    Select(driver.find_element_by_xpath('//*[@id="q_opt_ids19"]')).select_by_index(randRelationship)

    # Seeking
    success = False
    while success == False:
        for i in range(randNumSeeking):
            randSeeking = random.randint(1,8)
            try:
                driver.find_element_by_xpath('//*[@id="q_opt_ids16_chosen"]/ul[1]').click()
                driver.find_element_by_xpath('//*[@id="q_opt_ids16_chosen"]/div[1]/ul[1]/li['+ str(randSeeking) + ']').click()
            except:
                continue
            success = True

    # Seeking
    success = False
    while success == False:
        for i in range(randNumSeeking):
            randSeeking = random.randint(1,8)
            try:
                driver.find_element_by_xpath('//*[@id="q_opt_ids16_chosen"]/ul[1]').click()
                driver.find_element_by_xpath('//*[@id="q_opt_ids16_chosen"]/div[1]/ul[1]/li['+ str(randSeeking) + ']').click()
            except:
                continue
            success = True

    # Text entries
    driver.find_element_by_xpath('//*[@id="text_option_id18"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id20"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id21"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id22"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id23"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id24"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id25"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id26"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id27"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="text_option_id28"]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[2]/div[1]/div[2]/ul[1]/li[14]/span[2]/textarea[1]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")
    driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[2]/div[1]/div[2]/ul[1]/li[15]/span[2]/textarea[1]').send_keys("la;skdjf;asldkfj;alsdkfj;laskdjfl;askdjf;laksdjf;lkajsd;flkjasd;lkfjal;sdkjf;alskdjfpoqwiuerpoqwiueproiuqwptyi9028498234759839udwlkfjslvn,mznv,mnkashflkajhdfkljhqowieroiqwhsaldkfjhalskdjfhaklsdjhflkasjdhflajksdhlfkjhqwioeroiuoiudfalsdfklashdflkajshglasjkd;flkja;lsdkfjpaiowephalskdjfh;aksdhf;lakjdf;kja;sdfjk'")

    #photo
    if gender == "Man":
        photo_name = "Man" + str(randomPhoto) +".jpg"
        photo = os.path.join(os.getcwd(), "Man", photo_name)
        driver.find_element_by_name("photoUpload").send_keys(str(photo))
    elif gender == "Woman":
        photo_name = "Woman" + str(randomPhoto) +".jpg"
        photo = os.path.join(os.getcwd(), "Woman", photo_name)
        driver.find_element_by_name("photoUpload").send_keys(str(photo))
    elif gender == "Couple":
        photo_name = "Couple" + str(randomPhoto) +".jpg"
        photo = os.path.join(os.getcwd(), "Couple", photo_name)
        driver.find_element_by_name("photoUpload").send_keys(str(photo))

    driver.find_element_by_xpath('//*[@id="frm_u_profile"]/div[3]/div[1]/div[1]/ul[1]/li[3]/input[2]').click()
    time.sleep(1)
    driver.get("https://datingapp.wearetheducklings.com/")
    time.sleep(1)
    driver.find_element_by_id('wp-submit').click()
    time.sleep(1)

    print(username + " DONE")
    print(str(len(list_of_users) - numUsers) + " Remaining")
    numUsers += 1
    time.sleep(5)

print("Created " + str(numUsers) + " Users")
totalTime = time.time() - startTime
print("Execution took " + str(totalTime))

driver.quit()