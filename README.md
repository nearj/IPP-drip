# drip
 This site stands for term project of internet programming project in Univercity of Seoul.
**drip** has goal which simple image recognition with data base system with php. This prject inspired Optical Character Recognition(OCR), which could be found at [Wikipedia: Optical character recognition](https://en.wikipedia.org/wiki/Optical_character_recognition) or real time on screen translation.

# prerequisite
Recognition
---
..*[TensorFlow installation instructions](https://www.tensorflow.org/install/)
..*[Keras installation guide](https://keras.io/#guiding-principles)


# TODO List
- [ ] Recognition
- [ ] UI
- [ ] Backend system 

# Concept
![alt test](/Concept/Pictures/Concept.png)
 When a image uploaded by user(in User Interface), image process module digitalize the
image and compare that with saved **Diffusioned Data** in data base then, make decision
which is the most appropriate one(**Tag**). After make decision, the user interface system
interact with user that which the tag is right.

# Implemetation
 Implemetation could be vary and almost one take 2 layer matrix system before output, but
with some limitation, we take **Diffusion Data** when the pixel of a image has some value.

# Class Diagram
![alt text](/Concept/Pictures/abstract.jpg)
 Our project divided by three part, Image Processing, User Interface and Data Base. Above
diagram represent this modules usally one direction relation ship except User Interface
module and storage object in image processing.
